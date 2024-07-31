<?php

namespace MPHBW;

class WoocommerceHelper
{

	/**
	 * WooCommerce product and it's translations that represent MPHB payment.
	 *
	 * @var ReservationProduct
	 */
	private $reservationProduct;

	/**
	 *
	 *
	 * @var array
	 */
	protected $checkoutInfo = array();

	/**
	 * @param ReservationProduct $reservationProduct
	 */
	public function __construct($reservationProduct)
	{

		$this->reservationProduct = $reservationProduct;

		// Verify cart contents
		add_action('wp_loaded', array($this, 'verifyCart'), 10, 0);

		add_action('woocommerce_new_order_item', array($this, 'bindWooOrderWithPayment'), 10, 3);

		// Replace price on cart details && checkout
		add_action('woocommerce_before_calculate_totals', array($this, 'setPaymentPriceToOrderItem'), 10, 1);

		// Change Product price to MPHB Payment price in cart
		add_action('woocommerce_before_cart_contents', array($this, 'setPaymentPriceToOrderItem'), 10, 0);

		// Show payment link in admin order details.
		add_action('woocommerce_after_order_itemmeta', array($this, 'displayPaymentDataInOrder'), 10, 1);

		// @todo maybe use default_checkout_$input for make possible retrieving billing info from WooCommerce Customer data
		// Fill WooCommerce Checkout billing fields from booking customer data
		add_filter('woocommerce_checkout_get_value', array($this, 'fillCheckoutValue'), 10, 2);

		// Change product name in cart
		//		add_filter( 'woocommerce_cart_item_name', array( $this, 'replaceProductNameInCart' ), 10, 3 );
		// Hide product quantity in cart
		add_filter('woocommerce_checkout_cart_item_quantity', array($this, 'hideProductQuantity'), 10, 3);

		// Prevent customer to change product quantity
		add_filter('woocommerce_quantity_input_args', array($this, 'makeQuantityNotEditable'), 10, 2);

		// Prevent add product without attached payment to cart
		add_action('woocommerce_add_to_cart', array($this, 'preventAddToCartProductWithoutPayment'), 10, 6);

		// Hide product from shop
		add_action('pre_get_posts', array($this, 'hideMPHBProductFromShop'), 90, 1);

		// Force product invisibility
		add_filter('woocommerce_product_is_visible', array($this, 'forceProductInvisible'), 10, 2);

		// Force MPHB Product virtuality
		add_filter('woocommerce_is_virtual', array($this, 'forceProductVirtual'), 10, 2);

		//		add_filter( 'woocommerce_order_item_product', array( $this, 'filterProduct' ), 10, 2 );
		//		add_filter( 'woocommerce_product_is_taxable', array( $this, 'forceUntaxable' ), 10, 2 );
		//		add_filter( 'woocommerce_order_item_name', array( $this, 'changeOrderItemName' ), 10, 2 );
		// Hide mphb payment id item meta in admin
		add_filter('woocommerce_hidden_order_itemmeta', array($this, 'hidePaymentItemMeta'), 10, 1);

		// Hide Qty of MPHB Product
		add_filter('woocommerce_order_item_quantity_html', array($this, 'hideQtyOrderDetails'), 10, 2);

		/**
		 *
		 * @since 1.0.6
		 */
		add_filter('woocommerce_product_get_name', array($this, 'replaceWooOrderItemTitle'), 10, 2);

		/**
		 *
		 * @since 1.0.6
		 */
		add_filter('woocommerce_rest_prepare_report_orders', array($this, 'prepareRestReportOrdersData'), 10);
	}

	/**
	 *
	 * @param bool $visible
	 * @param int $productId
	 * @return bool
	 */
	public function forceProductInvisible($visible, $productId)
	{
		if ($this->reservationProduct->isReservationProductId($productId)) {
			$visible = false;
		}
		return $visible;
	}

	/**
	 *
	 * @param array $hiddenMeta
	 * @return array
	 */
	public function hidePaymentItemMeta($hiddenMeta)
	{
		$hiddenMeta[] = '_mphb_payment_id';
		return $hiddenMeta;
	}

	/**
	 *
	 * @param string $qtyHtml
	 * @param \WC_Order_Item $orderItem
	 * @return string
	 */
	public function hideQtyOrderDetails($qtyHtml, $orderItem)
	{
		$orderItemData = $orderItem->get_data();
		if (
			isset($orderItemData['product_id']) &&
			$this->reservationProduct->isReservationProductId($orderItemData['product_id'])
		) {
			$qtyHtml = '';
		}
		return $qtyHtml;
	}

	/**
	 *
	 * @param string $name
	 * @param \WC_Order_Item $orderItem
	 * @return string
	 */
	//	public function changeOrderItemName( $name, $orderItem ){
	//		$orderItemData = $orderItem->get_data();
	//		if ( isset( $orderItemData['product_id'] ) &&
	//			$this->reservationProduct->isReservationProductId( $orderItemData['product_id'] )
	//		) {
	//			$name = $this->getProductName();
	//		}
	//		return $name;
	//	}

	/**
	 *
	 * @return string
	 */
	//	private function getProductName(){
	//		return __( 'Booking', 'mphb-woocommerce' );
	//	}

	/**
	 *
	 * @param \WC_Product $product
	 * @param \WC_Order_Item $orderItem
	 */
	//	public function filterProduct( $product, $orderItem ){
	//
	//		if ( $this->reservationProduct->isReservationProductId( $product->get_id() ) ) {
	//			$product->set_name( $this->getProductName() );
	//		}
	//
	//		return $product;
	//	}

	/**
	 *
	 * @param bool $taxable
	 * @param \WC_Product $product
	 * @return boolean
	 */
	public function forceUntaxable($taxable, $product)
	{
		if ($this->reservationProduct->isReservationProductId($product->get_id())) {
			$taxable = false;
		}
		return $taxable;
	}

	/**
	 * Verifies the availability of all appointments that are in the cart
	 */
	public function verifyCart()
	{

		if (!WC()->cart) {
			return;
		}

		foreach (WC()->cart->get_cart() as $cartItemKey => $cartItem) {
			if ($this->reservationProduct->isReservationProductId($cartItem['product_id'])) {

				// Remove MPHB product without attached payment
				if (!array_key_exists('_mphb_payment_id', $cartItem)) {
					WC()->cart->remove_cart_item($cartItemKey);
					continue;
				}

				$paymentId	 = $cartItem['_mphb_payment_id'];
				$payment	 = MPHB()->getPaymentRepository()->findById($paymentId);

				if (!$payment) {
					WC()->cart->remove_cart_item($cartItemKey);
					continue;
				}

				// Remove product with abandoned payment from cart
				if ($payment->getStatus() === \MPHB\PostTypes\PaymentCPT\Statuses::STATUS_ABANDONED) {
					WC()->cart->remove_cart_item($cartItemKey);
					continue;
				}
			}
		}
	}

	/**
	 *
	 * @param boolean $virtual
	 * @param \WC_Product $product
	 * @return boolean
	 */
	public function forceProductVirtual($virtual, $product)
	{
		if ($this->reservationProduct->isReservationProductId($product->get_id())) {
			$virtual = true;
		}
		return $virtual;
	}

	/**
	 *
	 * @param \WP_Query $query
	 * @return \WP_Query
	 */
	public function hideMPHBProductFromShop($query)
	{

		if (is_admin()) {
			return $query;
		}

		if ($query->get('post_type') != 'product') {
			return $query;
		}

		if (!$this->reservationProduct->isSelected()) {
			return $query;
		}

		$postIn		 = $query->get('post__in');
		$postNotIn	 = (array) $query->get('post__not_in');

		if ($postIn) {
			$postIn	 = array_diff($postIn, $this->reservationProduct->getIds());
			$postIn	 = empty($postIn) ? array(0) : $postIn;
			$query->set('post__in', $postIn);
		} else {
			$postNotIn = array_merge($postNotIn, $this->reservationProduct->getIds());

			$query->set('post__not_in', $postNotIn);
		}

		return $query;
	}

	/**
	 *
	 * @param string $itemId
	 * @param \WC_Order_Item $orderItem
	 * @param int $orderId
	 */
	public function bindWooOrderWithPayment($itemId, $orderItem, $orderId)
	{
		if (isset($orderItem->legacy_values['_mphb_payment_id'])) {
			wc_update_order_item_meta($itemId, '_mphb_payment_id', $orderItem->legacy_values['_mphb_payment_id']);
			$payment = MPHB()->getPaymentRepository()->findById($orderItem->legacy_values['_mphb_payment_id']);
			if (!$payment) {
				return;
			}

			$payment->setTransactionId($orderId);
			$order = wc_get_order($orderId);

			$billingMetas = array(
				'_mphb_first_name'	 => $order->get_billing_first_name(),
				'_mphb_last_name'	 => $order->get_billing_last_name(),
				'_mphb_email'		 => $order->get_billing_email(),
				'_mphb_phone'		 => $order->get_billing_phone(),
				'_mphb_address1'	 => $order->get_billing_address_1(),
				'_mphb_address2'	 => $order->get_billing_address_2(),
				'_mphb_city'		 => $order->get_billing_city(),
				'_mphb_state'		 => $order->get_billing_state(),
				'_mphb_country'		 => $order->get_billing_country(),
				'_mphb_zip'			 => $order->get_billing_postcode()
			);

			foreach ($billingMetas as $metaName => $metaValue) {
				if (!empty($metaValue)) {
					update_post_meta($payment->getId(), $metaName, $metaValue);
				}
			}
		}
	}

	/**
	 * Change item price in cart.
	 *
	 * @param \WC_Cart $cart_object
	 */
	public function setPaymentPriceToOrderItem($cart = null)
	{

		if (!$cart) {
			$cart = wc()->cart;
		}

		foreach ($cart->cart_contents as $cartItemId => $cartItem) {
			if (isset($cartItem['_mphb_payment_id'])) {
				$paymentId	 = $cartItem['_mphb_payment_id'];
				$payment	 = MPHB()->getPaymentRepository()->findById($paymentId);
				if (!$payment) {
					return;
				}
				/** @var \WC_Product $cartItem['data'] */
				$cartItem['data']->set_price($payment->getAmount());
				break;
			}
		}
	}

	/**
	 * Display payment data in order details (wp-admin)
	 *
	 * @param int $itemId
	 */
	public function displayPaymentDataInOrder($itemId)
	{
		$paymentId = wc_get_order_item_meta($itemId, '_mphb_payment_id');

		if ($paymentId) {
			$editLink	 = mphb_get_edit_post_link_for_everyone($paymentId);
			$paymentLink = sprintf('<a href="%s">%s</a>', $editLink, $paymentId);

			echo "<br/>";
			printf(__('Hotel Booking Payment: %s', 'mphb-woocommerce'), $paymentLink);
		}
	}

	/**
	 *
	 * @param string|null $value
	 * @param string $fieldName
	 * @return string
	 */
	public function fillCheckoutValue($value, $fieldName)
	{
		if (empty($this->checkoutInfo) && ! empty(WC()->cart)) {
			
			foreach (WC()->cart->get_cart() as $cartItemId => $cartItem) {
				if (array_key_exists('_mphb_payment_id', $cartItem)) {
					$paymentId	 = $cartItem['_mphb_payment_id'];
					$payment	 = MPHB()->getPaymentRepository()->findById($paymentId);
					if (!$payment) {
						break;
					}

					$booking = MPHB()->getBookingRepository()->findById($paymentId);
					if (!$booking) {
						break;
					}

					$customer = $booking->getCustomer();

					$this->checkoutInfo = array(
						'billing_first_name' => $customer->getFirstName(),
						'billing_last_name'	 => $customer->getLastName(),
						'billing_email'		 => $customer->getEmail(),
						'billing_phone'		 => $customer->getPhone(),
						'billing_address_1'	 => $customer->getAddress1(),
						'billing_city'		 => $customer->getCity(),
						'billing_country'	 => $customer->getCountry(),
						'billing_state'		 => $customer->getState(),
						'billing_postcode'	 => $customer->getZip(),
					);
					break;
				}
			}
		}

		if (array_key_exists($fieldName, $this->checkoutInfo) && !empty($this->checkoutInfo[$fieldName])) {
			$value = $this->checkoutInfo[$fieldName];
		}

		return $value;
	}

	/**
	 *
	 * @param string $title
	 * @param array $cartItemData
	 * @param string $cartItemKey
	 * @return string
	 */
	//	public function replaceProductNameInCart( $title, $cartItemData, $cartItemKey ){
	//
	//		if ( $this->reservationProduct->isReservationProductId( $cartItemData['product_id'] ) ) {
	//			$title = $this-replaceProductNameInCart>getProductName();
	//		}
	//
	//		return $title;
	//	}

	/**
	 *
	 * @param string $quantity
	 * @param array $cartItemData
	 * @param string $cartItemKey
	 * @return boolean
	 *
	 */
	public function hideProductQuantity($quantity, $cartItemData, $cartItemKey)
	{

		if ($this->reservationProduct->isReservationProductId($cartItemData['product_id'])) {
			$quantity = '';
		}

		return $quantity;
	}

	/**
	 *
	 * @throws Exception
	 * @param string $cartItemKey
	 * @param int   $productId contains the id of the product to add to the cart.
	 * @param int   $quantity contains the quantity of the item to add.
	 * @param int   $variationId ID of the variation being added to the cart.
	 * @param array $variation attribute values.
	 * @param array $cartItemData extra cart item data we want to pass into the item.
	 * @return boolean
	 */
	public function preventAddToCartProductWithoutPayment($cartItemKey, $productId, $quantity, $variationId, $variation, $cartItemData)
	{

		// Prevent add to cart MPHB Product without attached payment
		if (
			$this->reservationProduct->isReservationProductId($productId) &&
			!isset($cartItemData['_mphb_payment_id'])
		) {
			throw new \Exception(__('Cheatin&#8217; uh?', 'mphb-woocommerce'));
		}

		foreach (wc()->cart->get_cart() as $existsCartItemKey => $exstsCartItemData) {
			// Skip current added item
			if ($existsCartItemKey == $cartItemKey) {
				continue;
			}

			if ($this->reservationProduct->isReservationProductId($exstsCartItemData['product_id'])) {
				throw new \Exception(__('Your cart contains product that cannot be purchased with other products. Finish your reservation first or empty the cart.', 'mphb-woocommerce'));
			}
		}
	}

	/**
	 * Change attr for WC quantity input
	 *
	 * @param array $args
	 * @param \WC_Product $product
	 * @return mixed
	 */
	public function makeQuantityNotEditable($args, $product)
	{
		if ($this->reservationProduct->isReservationProductId($product->get_id())) {
			$args['max_value']	 = $args['input_value'];
			$args['min_value']	 = $args['input_value'];
		}

		return $args;
	}

	/**
	 *
	 * @param string $name
	 * @param \WC_Product $item
	 *
	 * @since 1.0.6
	 *
	 * @return string
	 */
	public function replaceWooOrderItemTitle($name, $item)
	{
		$itemId = $item->get_id();
		$contentToReplace = MPHBW()->getSettings()->main()->getProductLabel();

		if (!$this->reservationProduct->isReservationProductId($itemId) || empty($contentToReplace)) {
			return $name;
		}

		if (WC()->cart !== null) {
			$cartItems = WC()->cart->get_cart();

			if (!empty($cartItems)) {

				$cartItem = array_filter($cartItems, function ($item) use ($itemId) {
					return $item['product_id'] == $itemId;
				});
				$cartItem = array_shift($cartItem);

				if ($cartItem) {
					$paymentId = isset($cartItem['_mphb_payment_id']) ? (int)$cartItem['_mphb_payment_id'] : 0;
					$bookingDetails = $this->getBookingDetailsByPaymentId($paymentId);

					if (!empty($bookingDetails)) {
						$name = $this->replaceProductLabelTag($contentToReplace, $bookingDetails);
					}
				}
			}
		}

		return $name;
	}

	/**
	 *
	 * @param \WP_REST_Response $responce
	 *
	 * @since 1.0.6
	 */
	function prepareRestReportOrdersData($responce)
	{

		if (isset($responce->data) && isset($responce->data['order_id'])) {
			$orderId = $responce->data['order_id'];
			$order = wc_get_order($orderId);

			if ($order) {
				$orderItemNames = [];
				$orderItems = $order->get_items();

				if (!empty($orderItems)) {
					foreach ($orderItems as $item) {
						$orderItemNames[$item->get_product_id()] = $item->get_name();
					}
				}

				if (isset($responce->data['extended_info']['products'])) {
					$products = $responce->data['extended_info']['products'];

					if (!empty($products)) {
						$products = array_map(function ($product) use ($orderItemNames) {
							$productId = $product['id'];

							if (!$this->reservationProduct->isReservationProductId($productId)) {
								return $product;
							}

							if (isset($orderItemNames[$productId])) {
								$product['name'] = $orderItemNames[$productId];
							}

							return $product;
						}, $products);

						$responce->data['extended_info']['products'] = $products;
					}
				}
			}
		}

		return $responce;
	}

	/**
	 *
	 * @param array $bookingDetails
	 *
	 * @since 1.0.6
	 *
	 * @return string
	 */
	protected function accommodationNamesToString($bookingDetails)
	{

		$roomTypesString = '';

		if (!empty($bookingDetails)) {
			if (!empty($bookingDetails['roomTypes'])) {
				foreach ($bookingDetails['roomTypes'] as $roomType) {
					$roomTypesString .= $roomType->getTitle() . ', ';
				}
			}
			$roomTypesString = trim($roomTypesString, ', ');
		}

		return $roomTypesString;
	}

	/**
	 *
	 * @param string $contentToReplace
	 * @param array $bookingDetails
	 *
	 * @since 1.0.6
	 *
	 * @return string
	 */
	protected function replaceProductLabelTag($contentToReplace, $bookingDetails)
	{
		$content = '';
		$tagsToReplace = mphbw_generate_tags_find_string();

		$content = preg_replace_callback($tagsToReplace, function ($match) use ($bookingDetails) {
			$tag = str_replace('%', '', $match[0]);

			$replaceText = '';

			switch ($tag) {
				case 'booking_id':
					$replaceText = $bookingDetails['bookingId'];
					break;
				case 'reserved_accommodation_names':
					$replaceText = $this->accommodationNamesToString($bookingDetails);
					break;
				case 'check_in_date':
					$replaceText = $bookingDetails['checkIn'];
					break;
				case 'check_out_date':
					$replaceText = $bookingDetails['checkOut'];
					break;
			}

			return $replaceText;
		}, $contentToReplace);

		return $content;
	}

	/**
	 *
	 * @param int $paymentId
	 *
	 * @since 1.0.6
	 *
	 * @return array
	 */
	public function getBookingDetailsByPaymentId($paymentId)
	{
		$bookingDetails = array();

		$payment = MPHB()->getPaymentRepository()->findById($paymentId);
		if (!$payment) {
			return $bookingDetails;
		}

		$bookingId = $payment->getBookingId();

		$booking = MPHB()->getBookingRepository()->findById($bookingId);
		if (!$booking) {
			return $bookingDetails;
		}

		$bookingDetails['bookingId'] = $bookingId;

		$reservedRooms = $booking->getReservedRooms();
		if (!empty($reservedRooms)) {
			$bookingDetails['count'] = count($reservedRooms);
			foreach ($reservedRooms as $key => $reservationRoom) {
				$roomType = MPHB()->getRoomTypeRepository()->findById($reservationRoom->getRoomTypeId());
				$roomType = apply_filters('_mphb_translate_room_type', $roomType, null);

				$bookingDetails['roomTypes'][] = $roomType;
			}
		}

		$bookingDetails['checkIn'] = \MPHB\Utils\DateUtils::formatDateWPFront($booking->getCheckInDate());
		$bookingDetails['checkOut'] = \MPHB\Utils\DateUtils::formatDateWPFront($booking->getCheckOutDate());

		return $bookingDetails;
	}
}
