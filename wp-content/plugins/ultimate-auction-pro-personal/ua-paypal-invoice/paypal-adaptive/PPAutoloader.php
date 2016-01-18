<?php
	 /**
      * Basic class-map auto loader generated by install.php.
	  * Do not modify.
	  */
	 class PPAutoloader {
	 	private static $map = array (
  'accountidentifier' => 'adaptive/lib/AdaptivePayments.php',
  'adaptivepaymentsservice' => 'adaptive/lib/AdaptivePaymentsService.php',
  'address' => 'adaptive/lib/AdaptivePayments.php',
  'addresslist' => 'adaptive/lib/AdaptivePayments.php',
  'authsignature' => 'core/lib/auth/PPAuth.php',
  'baseaddress' => 'adaptive/lib/AdaptivePayments.php',
  'cancelpreapprovalrequest' => 'adaptive/lib/AdaptivePayments.php',
  'cancelpreapprovalresponse' => 'adaptive/lib/AdaptivePayments.php',
  'clientdetailstype' => 'adaptive/lib/AdaptivePayments.php',
  'configuration' => 'Configuration.php',
  'confirmpreapprovalrequest' => 'adaptive/lib/AdaptivePayments.php',
  'confirmpreapprovalresponse' => 'adaptive/lib/AdaptivePayments.php',
  'conversionrate' => 'adaptive/lib/AdaptivePayments.php',
  'convertcurrencyrequest' => 'adaptive/lib/AdaptivePayments.php',
  'convertcurrencyresponse' => 'adaptive/lib/AdaptivePayments.php',
  'currencycodelist' => 'adaptive/lib/AdaptivePayments.php',
  'currencyconversion' => 'adaptive/lib/AdaptivePayments.php',
  'currencyconversionlist' => 'adaptive/lib/AdaptivePayments.php',
  'currencyconversiontable' => 'adaptive/lib/AdaptivePayments.php',
  'currencylist' => 'adaptive/lib/AdaptivePayments.php',
  'currencytype' => 'adaptive/lib/AdaptivePayments.php',
  'displayoptions' => 'adaptive/lib/AdaptivePayments.php',
  'errorlist' => 'adaptive/lib/AdaptivePayments.php',
  'executepaymentrequest' => 'adaptive/lib/AdaptivePayments.php',
  'executepaymentresponse' => 'adaptive/lib/AdaptivePayments.php',
  'feedisclosure' => 'adaptive/lib/AdaptivePayments.php',
  'formatterfactory' => 'core/lib/formatters/FormatterFactory.php',
  'fundingconstraint' => 'adaptive/lib/AdaptivePayments.php',
  'fundingplan' => 'adaptive/lib/AdaptivePayments.php',
  'fundingplancharge' => 'adaptive/lib/AdaptivePayments.php',
  'fundingsource' => 'adaptive/lib/AdaptivePayments.php',
  'fundingtypeinfo' => 'adaptive/lib/AdaptivePayments.php',
  'fundingtypelist' => 'adaptive/lib/AdaptivePayments.php',
  'getallowedfundingsourcesrequest' => 'adaptive/lib/AdaptivePayments.php',
  'getallowedfundingsourcesresponse' => 'adaptive/lib/AdaptivePayments.php',
  'getavailableshippingaddressesrequest' => 'adaptive/lib/AdaptivePayments.php',
  'getavailableshippingaddressesresponse' => 'adaptive/lib/AdaptivePayments.php',
  'getfundingplansrequest' => 'adaptive/lib/AdaptivePayments.php',
  'getfundingplansresponse' => 'adaptive/lib/AdaptivePayments.php',
  'getpaymentoptionsrequest' => 'adaptive/lib/AdaptivePayments.php',
  'getpaymentoptionsresponse' => 'adaptive/lib/AdaptivePayments.php',
  'getprepaymentdisclosurerequest' => 'adaptive/lib/AdaptivePayments.php',
  'getprepaymentdisclosureresponse' => 'adaptive/lib/AdaptivePayments.php',
  'getshippingaddressesrequest' => 'adaptive/lib/AdaptivePayments.php',
  'getshippingaddressesresponse' => 'adaptive/lib/AdaptivePayments.php',
  'getuserlimitsrequest' => 'adaptive/lib/AdaptivePayments.php',
  'getuserlimitsresponse' => 'adaptive/lib/AdaptivePayments.php',
  'initiatingentity' => 'adaptive/lib/AdaptivePayments.php',
  'institutioncustomer' => 'adaptive/lib/AdaptivePayments.php',
  //'invoicedata' => 'adaptive/lib/AdaptivePayments.php',
  //'invoiceitem' => 'adaptive/lib/AdaptivePayments.php',
  'ippcredential' => 'core/lib/auth/IPPCredential.php',
  'ippformatter' => 'core/lib/formatters/IPPFormatter.php',
  'ipphandler' => 'core/lib/handlers/IPPHandler.php',
  'ippthirdpartyauthorization' => 'core/lib/auth/IPPThirdPartyAuthorization.php',
  'mockoauthdatastore' => 'core/lib/auth/AuthUtil.php',
  'oauthconsumer' => 'core/lib/auth/PPAuth.php',
  'oauthdatastore' => 'core/lib/auth/PPAuth.php',
  'oauthexception' => 'core/lib/auth/PPAuth.php',
  'oauthrequest' => 'core/lib/auth/PPAuth.php',
  'oauthserver' => 'core/lib/auth/PPAuth.php',
  'oauthsignaturemethod' => 'core/lib/auth/PPAuth.php',
  'oauthsignaturemethod_hmac_sha1' => 'core/lib/auth/PPAuth.php',
  'oauthsignaturemethod_plaintext' => 'core/lib/auth/PPAuth.php',
  'oauthsignaturemethod_rsa_sha1' => 'core/lib/auth/PPAuth.php',
  'oauthtoken' => 'core/lib/auth/PPAuth.php',
  'oauthutil' => 'core/lib/auth/PPAuth.php',
  'payerror' => 'adaptive/lib/AdaptivePayments.php',
  'payerrorlist' => 'adaptive/lib/AdaptivePayments.php',
  'paymentdetailsrequest' => 'adaptive/lib/AdaptivePayments.php',
  'paymentdetailsresponse' => 'adaptive/lib/AdaptivePayments.php',
  'paymentinfo' => 'adaptive/lib/AdaptivePayments.php',
  'paymentinfolist' => 'adaptive/lib/AdaptivePayments.php',
  'payrequest' => 'adaptive/lib/AdaptivePayments.php',
  'payresponse' => 'adaptive/lib/AdaptivePayments.php',
  'phonenumber' => 'adaptive/lib/AdaptivePayments.php',
  'phonenumbertype' => 'adaptive/lib/AdaptivePayments.php',
  'postpaymentdisclosure' => 'adaptive/lib/AdaptivePayments.php',
  'postpaymentdisclosurelist' => 'adaptive/lib/AdaptivePayments.php',
  'ppapicontext' => 'core/lib/common/PPApiContext.php',
  'ppapiservice' => 'core/lib/PPAPIService.php',
  'pparrayutil' => 'core/lib/common/PPArrayUtil.php',
  'ppauthenticationhandler' => 'core/lib/handlers/PPAuthenticationHandler.php',
  'ppbaseservice' => 'core/lib/PPBaseService.php',
  'ppcertificateauthhandler' => 'core/lib/handlers/PPCertificateAuthHandler.php',
  'ppcertificatecredential' => 'core/lib/auth/PPCertificateCredential.php',
  'ppconfigmanager' => 'core/lib/PPConfigManager.php',
  'ppconfigurationexception' => 'core/lib/exceptions/PPConfigurationException.php',
  'ppconnectionexception' => 'core/lib/exceptions/PPConnectionException.php',
  'ppconnectionmanager' => 'core/lib/PPConnectionManager.php',
  'ppconstants' => 'core/lib/PPConstants.php',
  'ppcredentialmanager' => 'core/lib/PPCredentialManager.php',
  'ppgenericservicehandler' => 'core/lib/handlers/PPGenericServiceHandler.php',
  'pphttpconfig' => 'core/lib/PPHttpConfig.php',
  'pphttpconnection' => 'core/lib/PPHttpConnection.php',
  'ppinvalidcredentialexception' => 'core/lib/exceptions/PPInvalidCredentialException.php',
  'ppipnmessage' => 'core/lib/ipn/PPIPNMessage.php',
  'pplogginglevel' => 'core/lib/PPLoggingManager.php',
  'pploggingmanager' => 'core/lib/PPLoggingManager.php',
  'ppmerchantservicehandler' => 'core/lib/handlers/PPMerchantServiceHandler.php',
  'ppmessage' => 'core/lib/PPMessage.php',
  'ppmissingcredentialexception' => 'core/lib/exceptions/PPMissingCredentialException.php',
  'ppmodel' => 'core/lib/common/PPModel.php',
  'ppnvpformatter' => 'core/lib/formatters/PPNVPFormatter.php',
  'ppopenidaddress' => 'core/lib/auth/openid/PPOpenIdAddress.php',
  'ppopeniderror' => 'core/lib/auth/openid/PPOpenIdError.php',
  'ppopenidhandler' => 'core/lib/handlers/PPOpenIdHandler.php',
  'ppopenidsession' => 'core/lib/auth/openid/PPOpenIdSession.php',
  'ppopenidtokeninfo' => 'core/lib/auth/openid/PPOpenIdTokeninfo.php',
  'ppopeniduserinfo' => 'core/lib/auth/openid/PPOpenIdUserinfo.php',
  'ppplatformservicehandler' => 'core/lib/handlers/PPPlatformServiceHandler.php',
  'ppreflectionutil' => 'core/lib/common/PPReflectionUtil.php',
  'pprequest' => 'core/lib/PPRequest.php',
  'pprestcall' => 'core/lib/transport/PPRestCall.php',
  'ppsignatureauthhandler' => 'core/lib/handlers/PPSignatureAuthHandler.php',
  'ppsignaturecredential' => 'core/lib/auth/PPSignatureCredential.php',
  'ppsoapformatter' => 'core/lib/formatters/PPSOAPFormatter.php',
    'ppsubjectauthorization' => 'core/lib/auth/PPSubjectAuthorization.php',
  'pptokenauthorization' => 'core/lib/auth/PPTokenAuthorization.php',
  'pptransformerexception' => 'core/lib/exceptions/PPTransformerException.php',
  'ppuseragent' => 'core/lib/common/PPUserAgent.php',
  'pputils' => 'core/lib/PPUtils.php',
  'ppxmlfaultmessage' => 'core/lib/PPXmlFaultMessage.php',
  'ppxmlmessage' => 'core/lib/PPXmlMessage.php',
  'preapprovaldetailsrequest' => 'adaptive/lib/AdaptivePayments.php',
  'preapprovaldetailsresponse' => 'adaptive/lib/AdaptivePayments.php',
  'preapprovalrequest' => 'adaptive/lib/AdaptivePayments.php',
  'preapprovalresponse' => 'adaptive/lib/AdaptivePayments.php',
  'receiver' => 'adaptive/lib/AdaptivePayments.php',
  'receiverdisclosure' => 'adaptive/lib/AdaptivePayments.php',
  'receiverdisclosurelist' => 'adaptive/lib/AdaptivePayments.php',
  'receiveridentifier' => 'adaptive/lib/AdaptivePayments.php',
  'receiverinfo' => 'adaptive/lib/AdaptivePayments.php',
  'receiverinfolist' => 'adaptive/lib/AdaptivePayments.php',
  'receiverlist' => 'adaptive/lib/AdaptivePayments.php',
  'receiveroptions' => 'adaptive/lib/AdaptivePayments.php',
  'refundinfo' => 'adaptive/lib/AdaptivePayments.php',
  'refundinfolist' => 'adaptive/lib/AdaptivePayments.php',
  'refundrequest' => 'adaptive/lib/AdaptivePayments.php',
  'refundresponse' => 'adaptive/lib/AdaptivePayments.php',
  'requestenvelope' => 'adaptive/lib/AdaptivePayments.php',
  'senderdisclosure' => 'adaptive/lib/AdaptivePayments.php',
  'senderidentifier' => 'adaptive/lib/AdaptivePayments.php',
  'senderoptions' => 'adaptive/lib/AdaptivePayments.php',
  'setpaymentoptionsrequest' => 'adaptive/lib/AdaptivePayments.php',
  'setpaymentoptionsresponse' => 'adaptive/lib/AdaptivePayments.php',
  'shippingaddressinfo' => 'adaptive/lib/AdaptivePayments.php',
  'stateregulatoryagencyinfo' => 'adaptive/lib/AdaptivePayments.php',
  'taxiddetails' => 'adaptive/lib/AdaptivePayments.php',
  'userlimit' => 'adaptive/lib/AdaptivePayments.php',
  'warningdata' => 'adaptive/lib/AdaptivePayments.php',
  'warningdatalist' => 'adaptive/lib/AdaptivePayments.php',
);

		public static function loadClass($class) {
	        $class = strtolower(trim($class, '\\'));

    	    if (isset(self::$map[$class])) {
            	require dirname(__FILE__) . '/' . self::$map[$class];
        	}
    	}

		public static function register() {
	        spl_autoload_register(array(__CLASS__, 'loadClass'), true);
    	}
}