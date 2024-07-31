<?php

namespace MPHB;

class Recommendation {

	private $rooms = array(); // [%ID% => ['adults', 'children', 'count', 'left']]

	/**
	 * @param array $availableRooms [%ID% => %free count%]
	 */
	public function __construct( $availableRooms ) {
		foreach ( $availableRooms as $id => $freeCount ) {
			$this->rooms[ $id ]          = $this->getRoomCapacity( $id );
			$this->rooms[ $id ]['count'] = $freeCount;
		}
	}

	/**
	 * @param int $adults
	 * @param int $children
	 */
	public function generate( $adults, $children = 0, $strict = false ) {
		$rooms          = $this->rooms;
		$recommendation = array(); // [%ID%, %ID%, ...] - the list of IDs of the best variant for the searched capacity
		$variants       = array( 0 => 0 ); // [%adults count% => %children count%]
		$combinations   = array( 0 => array() ); // [%adults count% => %room IDs to get that count%]

		$this->filter( $rooms, $adults, $children );
		$this->sort( $rooms, $adults, $children );

		$recommendation = $this->greedySearch( $adults, $children, $rooms, $variants, $combinations );

		if ( empty( $recommendation ) ) {
			$recommendation = $this->exhaustiveSearch( $adults, $children, $rooms, $variants, $combinations );
		}

		if ( empty( $recommendation ) ) {
			$recommendation = $this->findSolution( $variants, $combinations, $adults, $children );
		}

		if ( empty( $recommendation ) && ! $strict ) {
			$recommendation = $this->findApproximately( $variants, $combinations, $adults, $children );
		}

		if ( empty( $recommendation ) && ! $strict ) {
			$recommendation = $this->findAnything( $variants, $combinations, $adults, $children );
		}

		// Convert [%ID%, %ID%, ...] into [%ID% => %count%]
		return $this->count( $recommendation );
	}

	private function greedySearch( $adults, $children, &$rooms, &$variants, &$combinations ) {
		$gotAdults   = 0;
		$gotChildren = 0;

		foreach ( $rooms as $id => &$room ) {
			$_adults   = $room['adults'];
			$_children = $room['children'];

			while ( $room['left'] > 0 ) {
				$room['left']--;

				if ( $_adults < $adults || $_children < $children ) {
					$previousSums = array_keys( $variants );
					$gotAdults   += $_adults;
					$gotChildren += $_children;
				} else {
					// The value is too big, so no need to calculate sums with all previous variants
					// Only add the value without any combinations, if it not exists yet
					$previousSums = array( 0 );
				}

				// Calculate all possible unique variants with new value
				foreach ( $previousSums as $sum ) {
					$__adults   = $sum + $_adults;
					$__children = $variants[ $sum ] + $_children;

					if ( ! isset( $variants[ $__adults ] ) ) {
						$variants[ $__adults ]       = $__children;
						$combinations[ $__adults ]   = $combinations[ $sum ];
						$combinations[ $__adults ][] = $id;

						// Recommendation found if we have enough places for adults/children
						// or no more need to search adults and have enough child places
						if (
							( $__adults == $adults && $__children >= $children )
							|| ( $adults == 0 && $__children >= $children )
						) {
							return $combinations[ $__adults ];
						}
					}
				}

				// If the total value is too big or this room can't fill child places,
				// then stop searching with this type of rooms
				if (
					( $gotAdults >= $adults && $gotChildren >= $children )
					|| ( $gotAdults >= $adults && $_children == 0 )
				) {
					$gotAdults   -= $_adults;
					$gotChildren -= $_children;
					break;
				}
			} // While have more rooms left
		} // For each room
		unset( $room );

		return array();
	}

	private function exhaustiveSearch( $adults, $children, &$rooms, &$variants, &$combinations ) {
		foreach ( $rooms as $id => &$room ) {
			while ( $room['left'] > 0 ) {
				$room['left']--;

				// Calculate all possible unique variants with new value
				$previousSums = array_keys( $variants ); // Can't just iterate through $variants in PHP 5 - will get an infitine cycle
				foreach ( $previousSums as $sum ) {
					$__adults   = $sum + $room['adults'];
					$__children = $variants[ $sum ] + $room['children'];

					if ( ! isset( $variants[ $__adults ] ) ) {
						$variants[ $__adults ]       = $__children;
						$combinations[ $__adults ]   = $combinations[ $sum ];
						$combinations[ $__adults ][] = $id;

						// Recommendation found if found enough places for adults/children
						// or no more need to search adults and have enough child places
						if (
							( $__adults == $adults && $__children >= $children )
							|| ( $adults == 0 && $__children >= $children )
						) {
							return $combinations[ $__adults ];
						}
					}
				}
			} // While have more rooms left
		} // For each room
		unset( $room );

		// Exact result was not found
		return array();
	}

	/**
	 * Search for solution, when we have enough places both for adults and for
	 * children.
	 */
	private function findSolution( $variants, $combinations, $adults, $children ) {
		$bestResult       = 0; // Best "adults" value
		$requiredCapacity = $adults + $children;
		$minOverflow      = PHP_INT_MAX; // Min difference between required value and variant

		foreach ( $variants as $gotAdults => $gotChildren ) {
			$gotCapacity = $gotAdults + $gotChildren;
			$gotOverflow = $gotCapacity - $requiredCapacity;

			if ( $gotAdults >= $adults && $gotChildren >= $children && $gotOverflow < $minOverflow ) {
				$bestResult  = $gotAdults;
				$minOverflow = $gotOverflow;
			}
		}

		return $combinations[ $bestResult ];
	}

	/**
	 * Search for variant, when we have enough places for adults and maximum
	 * places for children.
	 */
	private function findApproximately( $variants, $combinations, $adults, $children ) {
		$bestAdults   = PHP_INT_MAX; // $bestAdults = min( %variants% ), but $bestAdults >= $adults
		$bestChildren = 0; // $bestChildren = max( %variants% )

		foreach ( $variants as $gotAdults => $gotChildren ) {
			if ( $gotAdults >= $adults
				 && ( $gotChildren > $bestChildren || ( $gotChildren == $bestChildren && $gotAdults < $bestAdults ) )
			) {
				$bestAdults   = $gotAdults;
				$bestChildren = $gotChildren;
			}
		}

		return ( isset( $combinations[ $bestAdults ] ) ? $combinations[ $bestAdults ] : array() );
	}

	/**
	 * Find the variant with the biggest capacity.
	 */
	private function findAnything( $variants, $combinations, $adults, $children ) {
		$bestResult       = 0; // Best "adults" value
		$requiredCapacity = $adults + $children;
		$minOverflow      = PHP_INT_MAX; // Min difference between required value and variant

		krsort( $variants );

		foreach ( $variants as $gotAdults => $gotChildren ) {
			$gotCapacity = $gotAdults + $gotChildren;
			$gotOverflow = abs( $requiredCapacity - $gotCapacity );

			if ( $gotOverflow < $minOverflow ) {
				$bestResult  = $gotAdults;
				$minOverflow = $gotOverflow;
			}
		}

		return $combinations[ $bestResult ];
	}

	/**
	 * Filter excess free rooms. For example, no need to work with 10xTriple
	 * Room while only 10 places are required, it's enough 4 of them.
	 */
	private function filter( &$rooms, $adults, $children ) {
		foreach ( $rooms as &$room ) {
			if ( $room['adults'] > 0 || $room['children'] > 0 ) {
				$requiredForAdults   = ( $room['adults'] > 0 ? (int) ceil( $adults / $room['adults'] ) : 0 );
				$requiredForChildren = ( $room['children'] > 0 ? (int) ceil( $children / $room['children'] ) : 0 );
				$room['left']        = max( $requiredForAdults, $requiredForChildren );
				$room['left']        = min( $room['left'], $room['count'] );
			} else {
				$room['left'] = 0;
			}
		}
		unset( $room );
	}

	/**
	 * Sort from biggest to lowest capacity.
	 */
	private function sort( &$rooms, $adults, $children ) {
		// Sort, preserving keys
		uasort(
			$rooms,
			function ( $a, $b ) {
				if ( $a['adults'] != $b['adults'] ) {
					return ( $a['adults'] < $b['adults'] ? 1 : -1 );
				} else {
					if ( $a['children'] != $b['children'] ) {
						return ( $a['children'] < $b['children'] ? 1 : -1 );
					} else {
						return 0;
					}
				}
			}
		);
	}

	/**
	 * Calculate the count of each unique IDs in the array and group by ID.
	 *
	 * @return array [%ID% => %count%]
	 */
	private function count( $recommendation ) {
		$ids = array();
		foreach ( $recommendation as $id ) {
			if ( isset( $ids[ $id ] ) ) {
				$ids[ $id ]++;
			} else {
				$ids[ $id ] = 1;
			}
		}
		return $ids;
	}

	private function getRoomCapacity( $id ) {
		$room = MPHB()->getRoomTypeRepository()->findById( $id );
		return array(
			'adults'   => $room->getAdultsCapacity(),
			'children' => $room->getChildrenCapacity(),
		);
	}

}
