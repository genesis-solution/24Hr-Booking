<?php

namespace MPHB\Reports;

use MPHB\Reports\Data\ReportEarningsByDatesData;
use \MPHB\Admin\Fields\FieldFactory;

class EarningsReport extends AbstractReport {

	const REPORT_TYPE = 'earnings';

	/**
	 * @var string
	 */
	protected $currencySymbol;

	/**
	 * @var array
	 */
	protected $colors;

	/**
	 * @var array
	 */
	protected $showDataTypes;

	/**
	 * @var \ReportEarningsByDatesData
	 */
	protected $data;

	/**
	 * @var string
	 */
	protected $range;

	public function __construct( $atts ) {
		parent::__construct( $atts );

		$this->reportType = self::REPORT_TYPE;

		$this->currencySymbol = MPHB()->settings()->currency()->getBundle()->getSymbol( strtoupper( MPHB()->settings()->currency()->getCurrencyCode() ) );

		$this->colors = array(
			'confirmed' => array(
				'bars'   => 'rgb(7, 33, 30, 0.8)',
				'dashes' => '#0b3631',
				'line'   => '#0b3631',
			),
			'cancelled' => array(
				'bars'   => 'rgb(172, 172, 170, 0.8)',
				'dashes' => '#acacaa',
				'line'   => '#acacaa',
			),
			'pending'   => array(
				'bars'   => 'rgb(161, 132, 99, 0.8)',
				'dashes' => '#a18463',
				'line'   => '#a18463',
			),
			'abandoned' => array(
				'bars'   => 'rgb(112, 106, 117, 0.8)',
				'dashes' => '#706a75',
				'line'   => '#706a75',
			),
		);

		$this->showDataTypes = array( 'confirmed' );

		$this->data = new ReportEarningsByDatesData( $atts );
	}

	public function enqueueScripts() {
		wp_register_script( 'jquery-flot', MPHB()->getPluginUrl( 'vendors/jquery-flot/jquery.flot.js' ), array( 'jquery' ), '0.8.1' );
		wp_register_script( 'jquery-time-flot', MPHB()->getPluginUrl( 'vendors/jquery-flot/jquery.flot.time.js' ), array( 'jquery' ), '0.8.1' );
		wp_register_script( 'jquery-flot-dashes', MPHB()->getPluginUrl( 'vendors/jquery-flot-dashes/jquery.flot.dashes.js' ), array( 'jquery' ), '0.1.0' );
		wp_register_script( 'mphb-admin-reports', MPHB()->getPluginUrl( 'assets/js/admin/admin-reports.min.js' ), array( 'jquery' ), MPHB()->getVersion() );
		wp_enqueue_script( 'jquery-flot' );
		wp_enqueue_script( 'jquery-time-flot' );
		wp_enqueue_script( 'jquery-flot-dashes' );
		wp_enqueue_script( 'mphb-admin-reports' );

		$json = $this->prepareJsonData();

		wp_localize_script( 'mphb-admin-reports', 'ReportData', array( 'data' => $json ) );
	}

	private function translateDataFilters( $filter ) {
		$translation = '';
		switch ( $filter ) {
			case 'totalPrice':
				$translation = __( 'Total Sales', 'motopress-hotel-booking' );
				break;
			case 'totalWithoutTax':
				$translation = __( 'Total Without Taxes', 'motopress-hotel-booking' );
				break;
			case 'totalFees':
				$translation = __( 'Total Fees', 'motopress-hotel-booking' );
				break;
			case 'totalServices':
				$translation = __( 'Total Services', 'motopress-hotel-booking' );
				break;
			case 'totalDiscount':
				$translation = __( 'Total Discounts', 'motopress-hotel-booking' );
				break;
			case 'totalBookings':
				$translation = __( 'Total Bookings', 'motopress-hotel-booking' );
				break;
		}

		return $translation;
	}

	private function iterateDates( $bookings ) {
		$data       = array();
		$datesArray = $this->data->getDatesArray();

		foreach ( $datesArray as $date ) {
			$dateInMilliseconds = strtotime( $date ) * 1000;

			foreach ( $this->data->getDataFilters() as $filter ) {
				if ( isset( $bookings[ $date ] ) ) {
					$data[ $filter ][] = array( $dateInMilliseconds, $bookings[ $date ][ $filter ] );
				} else {
					$data[ $filter ][] = array( $dateInMilliseconds, 0 );
				}
			}
		}

		return $data;
	}

	public function preparePlotData() {
		$plotData = array();

		foreach ( $this->data->getReportData() as $dataType => $reportData ) {
			$plotData[ $dataType ] = $this->iterateDates( $reportData );
		}

		return $plotData;
	}

	private function getDashLength( $filter ) {
		switch ( $filter ) {
			case 'totalWithoutTax':
				$dashLength = array( 7, 7 );
				break;
			case 'totalFees':
				$dashLength = array( 4, 4 );
				break;
			case 'totalServices':
				$dashLength = array( 1, 1 );
				break;
			case 'totalDiscount':
				$dashLength = array( 1, 4 );
				break;
			default:
				$dashLength = array( 10, 10 );
				break;
		}

		return $dashLength;
	}

	/**
	 *
	 * @return int
	 */
	private function getBarWidth() {
		$interval = (array) $this->data->getDatesPeriod()->getDateInterval();

		return ( $interval['s'] + 60 * $interval['i'] + 3600 * $interval['h'] + 86400 * $interval['d'] + 2592000 * $interval['m'] + 946080000 * $interval['y'] ) * 800;
	}

	private function getDateTimeFormatOptions() {
		$formats['timeformat']  = '%e %b';
		$formats['tickSize']    = array( 1, 'day' );
		$formats['weeksOfYear'] = false;

		switch ( $this->data->getRange() ) {
			case 'today':
			case 'yesterday':
				$formats['timeformat'] = '%H:%M';
				$formats['tickSize']   = array( 1, 'hour' );
				break;
			case 'this_week':
			case 'last_week':
				$formats['timeformat'] = '%a %e %b';
				$formats['tickSize']   = array( 1, 'day' );
				break;
			case 'this_quarter':
			case 'last_quarter':
				$formats['weeksOfYear'] = true;
				$formats['tickSize']    = array( 7, 'day' );
				break;
			case 'last_thirty_days':
			case 'this_month':
			case 'last_month':
				$formats['timeformat'] = '%e %b';
				$formats['tickSize']   = array( 1, 'day' );
				break;
			case 'this_year':
			case 'last_year':
				$formats['timeformat'] = '%b %Y';
				$formats['tickSize']   = array( 1, 'month' );
				break;
			case 'custom':
				$datesDiff = date_diff( date_create( $this->data->getDateFrom() ), date_create( $this->data->getDateTo() ) );
				if ( isset( $datesDiff->days ) && $datesDiff->days > 90 ) {
					$formats['tickSize'] = array( 1, 'month' );
				} elseif ( isset( $datesDiff->days ) && $datesDiff->days > 31 ) {
					$formats['tickSize'] = array( 7, 'day' );
				} else {
					$formats['tickSize'] = array( 1, 'day' );
				}
				break;
		}

		return $formats;
	}

	protected function prepareJsonData() {
		$plotData   = $this->preparePlotData();
		$datesArray = $this->data->getDatesArray();
		$json       = array();
		$dashLength = array( 0, 0 );
		$barWidth   = 0;

		foreach ( $plotData as $dataType => $pd ) {

			$dataType = strtolower( $dataType );

			foreach ( $pd as $filter => $plotArray ) {
				if ( in_array( $filter, array( 'totalWithoutTax', 'totalFees', 'totalServices', 'totalDiscount' ) ) ) {
					$plotType   = 'dashes';
					$dashLength = $this->getDashLength( $filter );
				} elseif ( $filter == 'totalBookings' ) {
					$plotType = 'bars';
					$barWidth = $this->getBarWidth();
				} else {
					$plotType = 'line';
				}

				$json['plotData'][] = array(
					'dataType'   => $dataType,
					'dataFilter' => $filter,
					'plotType'   => $plotType,
					'plotArray'  => $plotArray,
					'barWidth'   => $barWidth,
					'dashLength' => $dashLength,
					'color'      => $this->colors[ $dataType ][ $plotType ],
				);
			}
		}

		$json['startDate'] = strtotime( $datesArray[0] ) * 1000;
		$json['endDate']   = strtotime( $datesArray[ count( $datesArray ) - 1 ] );

		switch ( $this->data->getRange() ) {
			case 'today':
			case 'yesterday':
				$correction      = '-1 hour';
				$json['endDate'] = strtotime( $correction, $json['endDate'] ) * 1000;
				break;
			case 'this_quarter':
			case 'last_quarter':
				$json['endDate'] = $json['endDate'] * 1000;
				break;
			case 'this_year':
			case 'last_year':
				$correction      = '-1 month';
				$json['endDate'] = strtotime( $correction, $json['endDate'] ) * 1000;
				break;
			default:
				$correction      = '-1 day';
				$json['endDate'] = strtotime( $correction, $json['endDate'] ) * 1000;
				break;
		}

		$formats                = $this->getDateTimeFormatOptions();
		$json['timeformat']     = $formats['timeformat'];
		$json['tickSize']       = $formats['tickSize'];
		$json['weeksOfYear']    = $formats['weeksOfYear'];
		$json['currencySymbol'] = $this->currencySymbol;

		return wp_json_encode( $json );
	}

	public function renderReportTitle() {
		$title = __( 'Revenue', 'motopress-hotel-booking' );

		echo sprintf( '<h3>%s</h3>', esc_html( $title ) );
	}

	public function renderFilters() {
		$this->renderDatesRangeFilters( $this->data->getRange() );
	}

	/**
	 *
	 * @param string $choosenRange
	 */
	public function renderDatesRangeFilters( $choosenRange ) {
		$datesRanges = ReportFilters::getDatesRanges();
		?>
		<div class="tablenav top">
			<div class="alignleft actions">
				<form id="mphb-graphs-filter" method="get">
					<input type="hidden" name="page" value="mphb_reports">
					<input type="hidden" name="report_type" value="<?php echo esc_attr( $this->reportType ); ?>">
					<?php
					if ( ! empty( $datesRanges ) ) {
						?>
						<select id="mphb-dates-range-select" name="range">
							<?php
							foreach ( $datesRanges as $datesRange ) {
								?>
								<option value="<?php echo esc_attr( $datesRange['type'] ); ?>" <?php echo $datesRange['type'] == $choosenRange ? 'selected="selected"' : ''; ?>><?php echo esc_html( $datesRange['description'] ); ?></option>
								<?php
							}
							?>
						</select>
						<?php
					}
					?>
					<div id="mphb-dates-range-show" class="mphb-dates-range-custom <?php echo $this->data->getRange() != 'custom' ? 'mphb-invisible' : ''; ?>">
						<?php
                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo FieldFactory::create(
							'date_from',
							array(
								'type'        => 'datepicker',
								'placeholder' => esc_attr__( 'Start date', 'motopress-hotel-booking' ),
								'size'        => 'wide',
								'readonly'    => false,
							),
							$this->data->getDateFrom()
						)->render();

                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo FieldFactory::create(
							'date_to',
							array(
								'type'        => 'datepicker',
								'placeholder' => esc_attr__( 'End date', 'motopress-hotel-booking' ),
								'size'        => 'wide',
								'readonly'    => false,
							),
							date( 'Y-m-d', strtotime( '-1 day', strtotime( $this->data->getDateTo() ) ) )
						)->render();
						?>
					</div>
					<input id="mphb-report-dates-filter-button" type="submit" class="button-secondary" value="<?php esc_attr_e( 'Apply', 'motopress-hotel-booking' ); ?>">
				</form>
			</div>
		</div>
		<?php
	}

	public function renderReportDataFilter() {
		?>
		<?php echo sprintf( '%s:', esc_html__( 'Show', 'motopress-hotel-booking' ) ); ?>
		<ul id="mphb-chart-data-filter">
			<?php
			foreach ( $this->data->getDataFilters() as $filter ) {
				$filterName = $this->translateDataFilters( $filter );
				?>
				<li data-datafilter="<?php echo esc_attr( $filter ); ?>">
					<label><input type="checkbox" class="mphb-data-filter-checkbox" <?php echo ( $filter == 'totalPrice' || $filter == 'totalBookings' ) ? 'checked="checked"' : ''; ?> /><?php echo esc_html( $filterName ); ?></label>
				</li>
				<?php
			}
			?>
		</ul>
		<?php
	}

	protected function prepareLegendData() {
		$legendData = array();
		$plotData   = $this->preparePlotData();

		foreach ( $this->data->getDataTypes() as $dataType => $label ) {
			$totalInfo   = array();
			$dataForType = isset( $plotData[ $dataType ] ) ? $plotData[ $dataType ] : array();
			foreach ( $this->data->getDataFilters() as $summary ) {
				$dataForTypeSummary = 0;
				if ( isset( $dataForType[ $summary ] ) ) {
					$dataForTypeSummary = array_reduce(
						array_map(
							function( $data ) {
								return $data[1];
							},
							$dataForType[ $summary ]
						),
						function( $i, $v ) {
							return $i += $v;
						}
					);
				}

				if ( $dataForTypeSummary > 0 ) {
					$totalInfo[ $summary ] = sprintf(
						'%1$s: %2$s',
						$this->translateDataFilters( $summary ),
						$summary != 'totalBookings' ? mphb_format_price( $dataForTypeSummary ) : $dataForTypeSummary
					);
				}
			}

			if ( empty( $totalInfo ) ) {
				$totalInfo['totalBookings'] = sprintf(
					'%1$s: %2$s',
					$this->translateDataFilters( 'totalBookings' ),
					0
				);
			}

			$legendData[] = array(
				'type'      => $dataType,
				'dataType'  => $label,
				'totalInfo' => $totalInfo,
				'color'     => $this->colors[ $dataType ]['line'],
			);
		}

		return $legendData;
	}

	public function prepareReportLegend() {
		$legendData = $this->prepareLegendData();

		$showDataTypes = $this->getShowDataTypes();

		$html = '';
		ob_start();
		?>
		<ul id="mphb-chart-legend">
			<?php
			foreach ( $legendData as $ld ) {
				$earned   = $ld['totalInfo'];
				$datatype = $ld['type'];
				?>
				<li class="mphb-chart-legend-item"
					data-dataType="<?php echo esc_attr( $datatype ); ?>"
					style="border-bottom-color: <?php echo esc_attr( $ld['color'] ); ?>;">
					<p class="mphb-chart-legend-item-type">
						<label><input type="checkbox" class="mphb-chart-legend-item-checkbox"
						<?php echo in_array( $datatype, $showDataTypes ) ? 'checked="checked"' : ''; ?>
						value="<?php echo esc_attr( $ld['dataType'] ); ?>" /><?php echo esc_html( $ld['dataType'] ); ?></label>
					</p>
					<?php
					if ( ! empty( $earned ) ) {
						?>
						<div class="mphb-chart-legend-item-earnings">
							<?php
							foreach ( $earned as $number ) {
								?>
								<p>
								<?php
                                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									echo $number;
								?>
									</p>
								<?php
							}
							?>
						</div>
						<?php
					}
					?>
				</li>
				<?php
			}
			?>
		</ul>
		<?php
		$html = ob_get_contents();
		if ( $html ) {
			ob_end_clean();
		}

		return $html;
	}

	public function renderReportLegend() {
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->prepareReportLegend();
	}

	protected function prepareReportInfoData() {
		$info = '';

		if ( $this->data->getRange() == 'today' || $this->data->getRange() == 'yesterday' ) {
			$info .= date( 'd M Y', strtotime( $this->data->getDateFrom() ) );
		} else {
			$info .= sprintf(
				__( 'From %s to %s', 'motopress-hotel-booking' ),
				date( 'd M Y', strtotime( $this->data->getDateFrom() ) ),
				date( 'd M Y', strtotime( '-1 day', strtotime( $this->data->getDateTo() ) ) )
			);
		}

		return $info;
	}

	public function renderReportInfo() {
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo sprintf( '%s', $this->prepareReportInfoData() );
	}

	public function renderReport() {
		?>
		<div id="mphb-earnings-report-chart">
			<div class="mphb-earnings-report-data-filter-wrapper">
				<div id="mphb-earnings-report-data-filter">
					<?php $this->renderReportDataFilter(); ?>
				</div>
			</div>
			<div class="mphb-earnings-report-legend-wrapper">
				<div id="mphb-earnings-report-legend">
					<?php $this->renderReportLegend(); ?>
				</div>
			</div>
			<div class="mphb-earnings-report-wrapper">
				<div id="mphb-earnings-report"></div>
				<div id="mphb-earnings-report-info">
					<?php $this->renderReportInfo(); ?>
				</div>
			</div>
		</div>
		<?php
	}

	public function setShowDataTypes( $dataType ) {
		array_push( $this->showDataTypes, $dataType );
		$this->showDataTypes = array_unique( $this->showDataTypes );
	}

	public function getShowDataTypes() {
		return $this->showDataTypes;
	}

	public function getData() {
		return $this->data;
	}
}

?>
