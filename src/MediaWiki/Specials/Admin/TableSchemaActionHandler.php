<?php

namespace SMW\MediaWiki\Specials\Admin;

use SMW\ApplicationFactory;
use SMW\MediaWiki\Renderer\HtmlFormRenderer;
use Onoi\MessageReporter\MessageReporterFactory;
use SMW\SQLStore\Installer;
use SMW\Message;
use SMW\Store;
use Html;
use WebRequest;

/**
 * @license GNU GPL v2+
 * @since   2.5
 *
 * @author mwjames
 */
class TableSchemaActionHandler {

	/**
	 * @var Store
	 */
	private $store;

	/**
	 * @var HtmlFormRenderer
	 */
	private $htmlFormRenderer;

	/**
	 * @var integer
	 */
	private $enabledFeatures = 0;

	/**
	 * @var OutputFormatter
	 */
	private $outputFormatter;

	/**
	 * @since 2.5
	 *
	 * @param Store $store
	 * @param HtmlFormRenderer $htmlFormRenderer
	 * @param OutputFormatter $outputFormatter
	 */
	public function __construct( Store $store, HtmlFormRenderer $htmlFormRenderer, OutputFormatter $outputFormatter ) {
		$this->store = $store;
		$this->htmlFormRenderer = $htmlFormRenderer;
		$this->outputFormatter = $outputFormatter;
	}

	/**
	 * @since 2.5
	 *
	 * @param integer $feature
	 *
	 * @return boolean
	 */
	public function isEnabledFeature( $feature ) {
		return ( $this->enabledFeatures & $feature ) != 0;
	}

	/**
	 * @since 2.5
	 *
	 * @param integer $enabledFeatures
	 */
	public function setEnabledFeatures( $enabledFeatures ) {
		$this->enabledFeatures = $enabledFeatures;
	}

	/**
	 * @since 2.5
	 *
	 * @return string
	 */
	public function getForm() {

		$this->htmlFormRenderer
			->setName( 'buildtables' )
			->setMethod( 'get' )
			->addHiddenField( 'action', 'updatetables' )
			->addHeader( 'h2', $this->getMessage( 'smw-admin-db' ) )
			->addParagraph( $this->getMessage( 'smw-admin-dbdocu' ) );

		if ( $this->isEnabledFeature( SMW_ADM_SETUP ) ) {
			$this->htmlFormRenderer
				->addHiddenField( 'udsure', 'yes' )
				->addSubmitButton(
					$this->getMessage( 'smw-admin-dbbutton' ),
					array(
						'class' => ''
					)
				);
		} else {
			$this->htmlFormRenderer
				->addParagraph( $this->getMessage( 'smw-admin-feature-disabled' ) );
		}

		return Html::rawElement( 'div', array(), $this->htmlFormRenderer->getForm() );
	}

	/**
	 * @since 2.5
	 *
	 * @param WebRequest $webRequest
	 *
	 * @return callable
	 */
	public function doUpdate( WebRequest $webRequest ) {

		if ( !$this->isEnabledFeature( SMW_ADM_SETUP ) ) {
			return;
		}

		$messageReporter = MessageReporterFactory::getInstance()->newObservableMessageReporter();
		$messageReporter->registerReporterCallback( array( $this, 'reportMessage' ) );

		$this->outputFormatter->setPageTitle( $this->getMessage( 'smw-admin-db' ) );
		$this->outputFormatter->addParentLink();

		$this->store->getOptions()->set( Installer::OPT_MESSAGEREPORTER, $messageReporter );

		$this->outputFormatter->addHTML( Html::rawElement( 'p', array(), $this->getMessage( 'smw-admin-permissionswarn' ) ) );

		$this->outputFormatter->addHTML( '<pre>' );

		// Output is generated by the injected 'installer.messagereporter'
		$result = $this->store->setup();

		$this->outputFormatter->addHTML( '</pre>' );

		if ( $result === true && $webRequest->getText( 'udsure' ) == 'yes' ) {
			$this->outputFormatter->addWikiText( '<p><b>' . $this->getMessage( 'smw-admin-setupsuccess' ) . "</b></p>" );
		}
	}

	/**
	 * @since 2.5
	 *
	 * @param string $message
	 */
	public function reportMessage( $message ) {
		$this->outputFormatter->addHTML( $message );
	}

	private function getMessage( $key, $type = Message::TEXT ) {
		return Message::get( $key, $type, Message::USER_LANGUAGE );
	}

}
