<?php


class TropipayoficialValidationModuleFrontController extends ModuleFrontController  {
    public function postProcess() {
        try{
            $idLog = generateIdLog();
            /** Log de Errores **/
            $logActivo = Configuration::get('TROPIPAY_LOG');
            escribirLog($idLog." -- "."Entramos en la validación del pedido",$logActivo);
            $accesoDesde = "";
            if (!empty($_POST)) {
                $accesoDesde = 'POST';
            } else if (!empty($_GET)) {
                $accesoDesde = 'GET';
            }


            $strrrr=file_get_contents('php://input');
            $ppd=json_decode($strrrr,true);

            $ds_amount = abs($ppd["data"]["originalCurrencyAmount"]);
            $ds_amountorig = $ppd["data"]["originalCurrencyAmount"];
            $total     = $ds_amount;
            $ds_order = $ppd["data"]["reference"];
            $ds_bankordercode = $ppd["data"]["bankOrderCode"];
            //$ds_amount = $ppd["data"]["originalCurrencyAmount"];
            //$ds_merchant_usermail = $settings['Mailuser_tropipay'];
            //$ds_merchant_userpassword = $settings['Password_tropipay'];
            $ds_merchant_usermail = Configuration::get('TROPIPAY_CLIENTID');
            $ds_merchant_userpassword = Configuration::get('TROPIPAY_CLIENTSECRET');
            $ds_reference=$ppd["data"]["reference"];
            $ds_currency = $ppd["data"]["currency"];
            $moneda=$ds_currency;
            $firma_remota = $ppd["data"]["signaturev2"];
            $firma_local=hash('sha256', $ds_bankordercode . $ds_merchant_usermail . $ds_merchant_userpassword . $ds_amountorig);

            escribirLog($idLog." -- "."Pedido de Tropipay: ".$ds_order,$logActivo);
            $pedidoSecuencial = $ds_order;
            $pedido = intval(substr($pedidoSecuencial, 0, 11));
            escribirLog($idLog." -- "."Pedido de Prestashop: ".$pedido,$logActivo);
            $error_pago = Configuration::get('TROPIPAY_ERROR_PAGO');
            $id_trans = $ppd["data"]["bankOrderCode"];
            escribirLog($idLog." -- "."ID trans: ".$id_trans,$logActivo);

            if($firma_local==$firma_remota) {
                //$order_id = tropipay_payments_get_order_id($ds_reference);

                $cart = new Cart($pedido);
                $tropipay = new tropipayoficial();
                
                $carrito_valido = true;
                $cliente = true;
                $mensajeError = "Errores validando el carrito: ";
                /** Validamos Objeto carrito **/
                if ($cart->id_customer == 0) {
                    escribirLog($idLog." -- "."Excepción validando el carrito. Cliente vacío. Puede no estar logueado, cargamos el guest.",$logActivo);
                    if ($cart->id_guest == 0) {
                        escribirLog($idLog." -- "."Error validando el carrito. Cliente vacío y Guest vacío.",$logActivo);
                        $mensajeError += "Cliente vacío | ";
                        $carrito_valido = false;
                    }
                    else {
                        $cliente = false;
                        escribirLog($idLog." -- "."Excepción validando el carrito CONTROLADA. Cliente vacío pero GUEST con datos.",$logActivo);
                    }
                }
                if ($cart->id_address_delivery == 0) {
                    escribirLog($idLog." -- "."Error validando el carrito. Dirección de envío vacía.",$logActivo);
                    $mensajeError += "Dirección de envío vacía | ";
                    $carrito_valido = false;
                }
                if ($cart->id_address_invoice == 0){
                    escribirLog($idLog." -- "."Error validando el carrito. Dirección de facturación vacía.",$logActivo);
                    $mensajeError += "Dirección de facturación vacía | ";
                    $carrito_valido = false;
                }
                if (!$tropipay->active) {
                    escribirLog($idLog." -- "."Error. Módulo desactivado.",$logActivo);
                    $mensajeError += "Módulo desactivado | ";
                    $carrito_valido = false;
                }
                if (!$carrito_valido){
                    escribirLog($idLog . " ++ " . serialize($cart), $logActivo);
                    $mensajeError += "REVISAR EN EL PORTAL DE ADMINISTRACION LA OPERACION '" . $pedido . "' YA QUE ES POSIBLE QUE HAYA SIDO CORRECTA";
                    $tropipay->validateOrder($pedido, _PS_OS_ERROR_, $total/100, $tropipay->displayName, $mensajeError);
                    Tools::redirect('index.php?controller=order&step=1');
                }
                /** Validamos Objeto cliente **/
                $customer = $cliente ? new Customer((int)$cart->id_customer) : new Guest((int)$cart->id_guest);
                
                if (!$cliente) {
                    escribirLog($idLog . " ++ " . serialize($customer), $logActivo);
                }
                    /** Donet **/
                    $address = new Address((int)$cart->id_address_invoice);
                    Context::getContext()->country = new Country((int)$address->id_country);
                    Context::getContext()->language = new Language((int)$cart->id_lang);
                    Context::getContext()->currency = new Currency((int)$cart->id_currency);
                    
                    if (!Validate::isLoadedObject($customer)) {
                        escribirLog($idLog." -- "."Error validando el cliente.",$logActivo);
                        Tools::redirect('index.php?controller=order&step=1');
                    }
                    
                    /** VALIDACIONES DE DATOS y LIBRERÍA **/
                    
                    $currencyOrig = new Currency($cart->id_currency);
                    $currency_decimals = is_array($currencyOrig) ? (int) $currencyOrig['decimals'] : (int) $currencyOrig->decimals;
                    $decimals = $currency_decimals * _PS_PRICE_DISPLAY_PRECISION_;
                    // ISO Moneda
                    $monedaOrig = $currencyOrig->iso_code;
                    if ($monedaOrig == 0 || $monedaOrig == null){
                        escribirLog($idLog." -- "."Error cargando moneda, utilizando la moneda recuperada.",$logActivo);
                        $monedaOrig = $moneda;
                    }
                    // DsResponse
                    $respuesta = (int)$respuesta;
                    
                    if ($monedaOrig == $moneda && $ppd["status"]=="OK" && $id_trans) {
                        /** Compra válida **/
                        $mailvars['transaction_id'] = (int)$id_trans;
                        $tropipay->validateOrder($cart->id, Configuration::get("TROPIPAY_ESTADO_PEDIDO"), $total/100, $tropipay->displayName, null, $mailvars, (int)$cart->id_currency, false, (property_exists($customer, "secure_key") && !is_null($customer->secure_key)) ? $customer->secure_key : false);
                        escribirLog($idLog." -- "."El pedido con ID de carrito " . $cart->id . " (" . $pedido . ") es válido y se ha registrado correctamente.",$logActivo);
                        echo "Pedido validado con éxito";
                        exit();
                    } else {
                        if (!($monedaOrig == $moneda)) {
                            escribirLog($idLog." -- "."La moneda no coincide. ($monedaOrig : $moneda)",$logActivo);
                        }
                        if (!$id_trans){
                            escribirLog($idLog." -- "."Ds_AuthorisationCode inválido. ($id_trans)",$logActivo);
                        }
                        if ($error_pago=="no"){
                            /** se anota el pedido como no pagado **/
                            $tropipay->validateOrder($pedido, _PS_OS_ERROR_, 0, $tropipay->displayName, 'errores:'.$ppd["status"]);
                        }
                        escribirLog($idLog." -- "."El pedido con ID de carrito " . $pedido . " es inválido.",$logActivo);
                    }
            }
            else {
                if ($accesoDesde === 'POST') {
                    escribirLog($idLog." -- "."La firma no coincide.",$logActivo);
                    if ($error_pago=="no"){
                        /** se anota el pedido como no pagado **/
                        $tropipay->validateOrder($pedido, _PS_OS_ERROR_, 0, $tropipay->displayName, 'errores:'.$respuesta);
                    }
                } else if ($accesoDesde === 'GET') {
                    Tools::redirect('index.php?controller=order&step=1');
                }
            }

                

            
            
        }
        catch (Exception $e){
            $idLogExc = generateIdLog();
            escribirLog($idLogExc." -- Excepcion en la validacion: ".$e->getMessage(),$logActivo);
            die("Excepcion en la validacion");
        }
    }
}