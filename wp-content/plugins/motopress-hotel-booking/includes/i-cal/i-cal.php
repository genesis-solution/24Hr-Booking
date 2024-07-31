<?php

namespace MPHB\iCal;

use \MPHB\Libraries\iCalendar\ZCiCal;
use \MPHB\Libraries\iCalendar\ZCiCalNode;
use \MPHB\Libraries\iCalendar\ZCiCalDataNode;

class iCal extends ZCiCal {

	/**
	 *
	 * @return ZCiCalNode[]
	 */
	public function getEvents() {
		if ( $this->countEvents() == 0 ) {
			return array();
		}

		$events = array();

		$event = $this->getFirstEvent();
		while ( $event ) {
			$events[] = $event;
			$event    = $this->getNextEvent( $event );
		}

		return $events;
	}

	/**
	 *
	 * @return array
	 */
	public function getEventsData( $roomId ) {
		$events = $this->getEvents();
		$prodid = $this->getProdid();
		$values = array();

		foreach ( $events as $event ) {
			$parsed = $this->parseEvent( $event );

			// Is parsed valid?
			if ( isset( $parsed['checkIn'] ) && isset( $parsed['checkOut'] ) ) {
				$parsed['prodid'] = $prodid;
				$parsed['roomId'] = $roomId;
				$values[]         = $parsed;
			}
		}

		return $values;
	}

	/**
	 *
	 * @param ZCiCalNode $event
	 *
	 * @return array Event values in format:
	 *     [
	 *         "uid"             => %UID%,
	 *         "checkIn"         => "2017-08-18", // "Y-m-d"
	 *         "checkOut"        => "2017-08-19", // "Y-m-d"
	 *         "summary"         => empty string or summary wrapped in "",
	 *         "description"     => empty string or description wrapped in ""
	 *     ]
	 */
	private function parseEvent( $event ) {
		$values = array(
			// Add optional node
			'uid'         => null, // Use null to recognize empty UIDs from non-existent
			'summary'     => '',
			'description' => '',
		);

		foreach ( $event->data as $name => $node ) {
			$name  = strtoupper( $name );
			$value = $node->getValues();

			// Convert all dates from format "DATE:20170818" into "2017-08-18"
			if ( $name == 'DTSTART' || $name == 'DTEND' ) {
				$matched = (bool) preg_match( '/(\d{4})(\d{2})(\d{2})/', $value, $date ); // ["20170817", "2017", "08", "18"]
				if ( $matched ) {
					array_shift( $date ); // ["2017", "08", "18"]
					$value = implode( '-', $date ); // "2017-08-18"
				} else {
					// Where is the date, Billy?
					continue;
				}
			}

			switch ( $name ) {
				case 'UID':
					$values['uid'] = $value;
					break;

				case 'DTSTART':
					$values['checkIn'] = $value;

					if ( isset( $values['checkOut'] ) ) {
						break;
					}

				case 'DTEND':
					$values['checkOut'] = $value;
					break;

				case 'SUMMARY':
					$values['summary'] = '"' . $value . '"';
					break;

				case 'DESCRIPTION':
					$values['description'] = '"' . $value . '"';
					break;
			}
		} // For each event attribute

		return $values;
	}

	/**
	 *
	 * @return string
	 */
	public function getProdid() {
		return isset( $this->tree->data['PRODID'] ) ? $this->tree->data['PRODID']->getValues() : '';
	}

	public function setProdid( $prodid ) {
		if ( isset( $this->tree->data['PRODID'] ) ) {
			$prodidNode        = $this->curnode->data['PRODID'];
			$prodidNode->value = array( $prodid );
		} else {
			$prodidNode                                    = new ZCiCalDataNode( 'PRODID:' . $prodid );
			$this->curnode->data[ $prodidNode->getName() ] = $prodidNode;
		}
	}

	public function removeMethodProperty() {
		if ( isset( $this->curnode->data['METHOD'] ) ) {
			unset( $this->curnode->data['METHOD'] );
		}
	}

}
