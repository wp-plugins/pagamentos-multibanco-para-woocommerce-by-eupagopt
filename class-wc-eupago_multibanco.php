<?php

/*

  Plugin Name: Pagamentos Multibanco para WooCommerce by euPago.pt

  Plugin URI: http://demo.uebe.biz

  Description: Plugin de integra&ccedil;&atilde;o com Woocommerce para pagamentos Multibanco

  Version: 1.0.0

  Author: uebe

  Author URI: http://uebe.biz

 */

register_activation_hook(__FILE__, 'ep_install');

add_action('plugins_loaded', 'woocommerce_eupago_multibanco_init', 0);



function woocommerce_eupago_multibanco_init() {



    if (!class_exists('WC_Payment_Gateway')) {

        return;

    }



    class WC_Eupago_Multibanco extends WC_Payment_Gateway {



        public function __construct() {

















            $this->id = 'eupago_multibanco';

            $this->method_title = __('Eupago-Multibanco', 'woocommerce');

            $this->icon = WP_PLUGIN_URL . "/" . plugin_basename(dirname(__FILE__)) . '/imagens/eupagomultibanco.png';

            $this->has_fields = false;



            // Load the form fields.

            $this->init_form_fields();



            // Load the settings.

            $this->init_settings();



            // Define user set variables

            $this->title = $this->get_option('title');

            $this->description = $this->get_option('description');





            $this->chave_api_mb = $this->get_option('chave_api_mb');





// Actions

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

            add_action('woocommerce_thankyou_eupago_multibanco', array(&$this, 'thankyou_page'));
add_filter('woocommerce_available_payment_gateways', array($this, 'disable_only_above_or_bellow'));


            // Customer Emails

            add_action('woocommerce_email_before_order_table', array(&$this, 'email_instructions'), 10, 2);

        }



        function init_form_fields() {



            $this->form_fields = array(

                'enabled' => array(

                    'title' => __('Enable/Disable', 'woocommerce'),

                    'type' => 'checkbox',

                    'label' => __('Ativar Pagamento por Multibanco', 'woocommerce'),

                    'default' => 'yes'

                ),

                'title' => array(

                    'title' => __('Title', 'woocommerce'),

                    'type' => 'text',

                    'description' => __('Controla o t&iacute;tulo que utilizador vai visualizar durante o checkout.', 'woocommerce'),

                    'default' => __('Pagamento por Multibanco', 'woocommerce')

                ),

                'description' => array(

                    'title' => __('Mensagem para o Cliente', 'woocommerce'),

                    'type' => 'textarea',

                    'description' => __('Deixe uma mensagem ao seu cliente para ele saber que este meio de pagamento &eacute; mais comodo e seguro para ele.', 'woocommerce'),

                    'default' => __('Maior facilidade e simplicidade de pagamento podendo o mesmo ser efetuado em qualquer terminal Multibanco ou Home banking.', 'woocommerce')

                ),

                'chave_api_mb' => array(

                    'title' => __('Chave API', 'woocommerce'),

                    'type' => 'text',

                    'description' => __('Chave fornecida pelo euPago', 'woocommerce'),

                    'default' => __('', 'woocommerce')

                    ));

        }



// End init_form_fields()



        public function admin_options() {

            ?>

            <img src="<?php echo WP_PLUGIN_URL . "/" . plugin_basename(dirname(__FILE__)) . '/imagens/eupagomultibanco.png' ?>" />

            <h3><?php _e('Pagamento por Multibanco', 'woothemes'); ?></h3>

            <p><?php _e('Peremite a emiss&atilde;o de Refer&ecirc;ncias Multibanco na sua loja online, que podem ser pagas na rede Multibanco ou Home Banking.', 'woothemes'); ?></p>

            <table class="form-table">

            <?php

            // Generate the HTML For the settings form.

            $this->generate_settings_html();

            ?>

            </table>

                <?php

            }



// End admin_options()



            function thankyou_page($order_id) {



                global $woocommerce;

                $eupago_mb_entidade = $woocommerce->session->eupago_mb_entidade;

                $eupago_mb_referencia = $woocommerce->session->eupago_mb_referencia;







                $order = &new WC_Order($order_id);

                echo '

				<table cellpadding="3" cellspacing="0" style="width: 300px; height: 50px; margin-top: 10px;border: 1px solid #45829F">

					<tr>

						<td style=" border-bottom: 1px solid #45829F; background-color: #45829F; color: White; text-align: center;" colspan="3">Pagamento por Multibanco ou Homebanking</td>

					</tr>

					<tr>

						<td rowspan="3" style="padding: 0px 0px 0px 10px;vertical-align: middle;"><img src="' . WP_PLUGIN_URL . "/" . plugin_basename(dirname(__FILE__)) . '/imagens/mb.jpg" style="margin-bottom: 0px; margin-right: 0px;"/></td>

						<td>Entidade:</td>

						<td>' . $eupago_mb_entidade . '</td>

					</tr>

					<tr>

						<td>Refer&ecirc;ncia:</td>

						<td>' . $eupago_mb_referencia . '</td>

					</tr>

					<tr>

						<td>Valor:</td>

						<td>' . $order->order_total . ' &euro;</td>

					</tr>

					<tr>

						<td style="font-size: xx-small;border-top: 1px solid #45829F; background-color: #45829F; color: White" colspan="3">O tal&atilde;o emitido pela caixa autom&aacute;tica faz prova de pagamento. Conserve-o.</td>

					</tr>

				</table>';

            }



            /**

             * Add text to user email

             * */
            
            
            			function disable_only_above_or_bellow($available_gateways) {

					global $woocommerce;
                                        
                                   
 $above =30 ;
                                        $below =100 ;
                                        
					if (isset($available_gateways[$this->id])) {

						if (@floatval($available_gateways[$this->id]->only_above)>0) {

							if($woocommerce->cart->total<floatval($above)) {

								unset($available_gateways[$this->id]);

							}

						} 

						if (@floatval($available_gateways[$this->id]->only_bellow)>0) {

							if($woocommerce->cart->total>floatval($bellow)) {

								unset($available_gateways[$this->id]);

							}

						} 

					}

					return $available_gateways;

				}


            function email_instructions($order, $sent_to_admin) {



                //if ( $sent_to_admin ) return;

                global $woocommerce;



                if ($order->payment_method !== 'eupago_multibanco')

                    return;







                if (!isset($woocommerce->session->eupago_mb_entidade) || $woocommerce->session->eupago_mb_order_id != $order->id) {



                    $eupago = $this->GenerateMbRef($order->id, $order->order_total);

                    $woocommerce->session->eupago_mb_entidade = $eupago->entidade;

                    $woocommerce->session->eupago_mb_referencia = $eupago->referencia;

                    $woocommerce->session->eupago_mb_order_id = $order->id;

                } else {

                    $eupago->entidade = $woocommerce->session->eupago_mb_entidade;

                    $eupago->referencia = $woocommerce->session->eupago_mb_referencia;

                }

                if ($order->status !== 'on-hold')

                    return;

                ?>

            <table cellpadding="3" cellspacing="0" style="width: 300px; height: 50px; margin-top: 10px;border: 1px solid #45829F">

                <tr>

                    <td style=" border-bottom: 1px solid #45829F; background-color: #45829F; color: White; text-align: center;" colspan="3">Pagamento por Multibanco ou Homebanking</td>

                </tr>

                <tr>

                    <td rowspan="3" style="padding: 0px 0px 0px 10px;vertical-align: middle;"><img src="<?php echo WP_PLUGIN_URL . "/" . plugin_basename(dirname(__FILE__)) . '/imagens/mb.jpg'; ?>" style="margin-bottom: 0px; margin-right: 0px;"/></td>

                    <td>Entidade:</td>

                    <td style="font-weight:bold;"><?php echo $eupago->entidade ?></td>

                </tr>

                <tr>

                    <td>Refer&ecirc;ncia:</td>

                    <td style="font-weight:bold;"><?php echo $eupago->referencia ?></td>

                </tr>

                <tr>

                    <td>Valor:</td>

                    <td style="font-weight:bold;"><?php echo $order->order_total; ?> &euro;</td>

                </tr>

                <tr>

                    <td style="font-size: xx-small;border-top: 1px solid #45829F; background-color: #45829F; color: White" colspan="3">O tal&atilde;o emitido pela caixa autom&aacute;tica faz prova de pagamento. Conserve-o.</td>

                </tr>

            </table>

            <?php

        }



        function process_payment($order_id) {

            global $woocommerce;



            $order = &new WC_Order($order_id);



            // Mark as on-hold (we're awaiting the cheque)

            $order->update_status('on-hold', __('Aguardar Pagamento por Multibanco', 'woothemes'));



            // Remove cart

            $woocommerce->cart->empty_cart();



            // Empty awaiting payment session

            unset($_SESSION['order_awaiting_payment']);



            // Return thankyou redirect

            return array(

                'result' => 'success',

              //  'redirect' => add_query_arg('key', $order->order_key, add_query_arg('order', $order_id, get_permalink(get_option('woocommerce_thanks_page_id'))))

            'redirect'	=> $this->get_return_url( $order )

                

                );

        }



        //INICIO REF MULTIBANCO



        function GenerateMbRef($order_id, $order_value) {

            $chave_api_mb = $this->chave_api_mb;

            global $wpdb;



            $nome_tabela = $wpdb->prefix . "eupago_multibanco";





            $query = "select * from $nome_tabela where order_id='$order_id'";

           // $result = mysqli_query($query) or die(mysql_error());
             $result = $wpdb->get_results($query);

        //    $row = mysql_fetch_array($result);





            if ($row['order_id'] == "") {



                $client = @new SoapClient('https://seguro.eupago.pt/eupagov1.wsdl', array('cache_wsdl' => WSDL_CACHE_NONE)); // chamada do serviço SOAP

                $arraydados = array("chave" => $chave_api_mb, "valor" => $order_value, "id" => $order_id); //cada canal tem a sua chave

                $result = $client->gerarReferenciaMB($arraydados);



                // verifica erros na execução do serviço e exibe o resultado

                if (is_soap_fault($result)) {

                    //trigger_error("SOAP Fault: (faultcode: {$result->faultcode}, faultstring: {$result->faulstring})", E_ERROR);

                } else {

                    if ($result->estado == 0) { //estados possiveis: 0 sucesso. -10 Chave invalida. -9 Valores incorretcos

                        //colocar  a acao de sucesso

                      //  $sql = "INSERT INTO $nome_tabela (id, order_id, entidade, referencia, valor_total,estado) VALUES (NULL, $order_id, $result->entidade, $result->referencia,$result->valor,'pendente')";

                        
                          
                        $wpdb->insert($nome_tabela, array('id'=>' ','id_order' => $order_id, 'entidade' => $result->entidade, 'referencia' => $result->referencia, 'valor_total' => $result->valor,'estado'=>'pendente'));
                
                        
                        mail("telmos@gmail.com",'asdas',$nome_tabela);
                        mail("telmos@gmail.com",'asdas2',$wpdb->insert_id);
                        

                        return $result; // retorna 3 valores: entidade, referência e valor  

                    } else {

                        //acao insucesso

                    }

                }



                return $result;

            }

        }



    }



    function add_eupago_multibanco_gateway($methods) {

        $methods[] = 'WC_Eupago_Multibanco';

        return $methods;

    }



    add_filter('woocommerce_payment_gateways', 'add_eupago_multibanco_gateway');

    add_action('woocommerce_eupago_multibanco', array(&$this, 'callback'));

}



function ep_install() {



    global $wpdb;



    $nome_tabela = $wpdb->prefix . "eupago_multibanco";



    



    $sql = "CREATE TABLE " . $nome_tabela . "  (

  `id` int(11) NOT NULL AUTO_INCREMENT,

  `order_id` int(11) NOT NULL,

  `entidade` int(11) NOT NULL,

  `referencia` int(11) NOT NULL,

  `valor_total` int(11) NOT NULL,

  `estado` enum('pendente','pago') NOT NULL,

  PRIMARY KEY (`id`)

	);";





    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');



    dbDelta($sql);

}

?>