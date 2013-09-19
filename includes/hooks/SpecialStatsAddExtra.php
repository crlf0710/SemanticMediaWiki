<?php

namespace SMW;

use SMWDataValueFactory;

use Language;

/**
 * SpecialStatsAddExtra hook
 *
 * @file
 *
 * @license GNU GPL v2+
 * @since   1.9
 *
 * @author mwjames
 */

/**
* Add extra statistic at the end of Special:Statistics
*
* @see http://www.mediawiki.org/wiki/Manual:Hooks/SpecialStatsAddExtra
 *
 * @ingroup Hook
 */
class SpecialStatsAddExtra extends FunctionHook {

	/** @var array */
	protected $extraStats = null;

	/** @var string */
	protected $version = null;

	/** @var Language */
	protected $userLanguage;

	/** @var string[] */
	protected $legacyMessageMapper = array(
		'PROPUSES'   => 'smw-statistics-property-instance',
		'USEDPROPS'  => 'smw-statistics-property-total-legacy',
		'OWNPAGE'    => 'smw-statistics-property-page',
		'DECLPROPS'  => 'smw-statistics-property-type',
		'SUBOBJECTS' => 'smw-statistics-subobject-count',
		'QUERY'      => 'smw-statistics-query-inline',
		'CONCEPTS'   => 'smw-statistics-concept-count-legacy'
	);

	/** @var string[] */
	protected $messageMapper = array(
		'PROPUSES'   => 'smw-statistics-property-instance',
		'USEDPROPS'  => 'smw-statistics-property-total',
		'OWNPAGE'    => 'smw-statistics-property-page',
		'DECLPROPS'  => 'smw-statistics-property-type',
		'SUBOBJECTS' => 'smw-statistics-subobject-count',
		'QUERY'      => 'smw-statistics-query-inline',
		'CONCEPTS'   => 'smw-statistics-concept-count'
	);

	/**
	 * @since  1.9
	 *
	 * @param array &$extraStats
	 * @param string $version
	 * @param Language $userLanguage User language
	 */
	public function __construct( array &$extraStats, $version, Language $userLanguage ) {
		$this->extraStats =& $extraStats;
		$this->version = $version;
		$this->userLanguage = $userLanguage;
	}

	/**
	 * @since 1.9
	 *
	 * @return true
	 */
	protected function copyStatistics() {

		$statistics = $this->getDependencyBuilder()->newObject( 'Store' )->getStatistics();

		$this->extraStats['smw-statistics'] = array();

		foreach ( $this->messageMapper as $key => $message ) {

			if ( isset( $statistics[$key] ) ) {
				$this->extraStats['smw-statistics'][ $message ] = $statistics[$key];
			}
		}

		$this->extraStats['smw-statistics']['smw-statistics-datatype-count'] = count( SMWDataValueFactory::getKnownTypeLabels() );

		return true;

	}

	/**
	 * Legacy approach to display statistical items for all MW 1.21- versions
	 *
	 * @since 1.9
	 *
	 * @return true
	 */
	protected function copyLegacyStatistics() {

		$statistics = $this->getDependencyBuilder()->newObject( 'Store' )->getStatistics();

		foreach ( $this->legacyMessageMapper as $key => $message ) {

			if ( isset( $statistics[$key] ) ) {
				$this->extraStats[wfMessage( $message )->text()] = $this->userLanguage->formatNum( $statistics[$key] );
			}
		}

		return true;
	}

	/**
	 * @see FunctionHook::process
	 *
	 * @since 1.9
	 *
	 * @return true
	 */
	public function process() {
		return version_compare( $this->version, '1.21', '<' ) ? $this->copyLegacyStatistics() : $this->copyStatistics();
	}

}