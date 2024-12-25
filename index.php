<?php
/*
Name: Printful
Description: Integrated Printful work with Lumise and Woocommerce
Version: 1.0
Compatible: 1.9.5
*/

class lumise_addon_printful extends lumise_addons {

	protected $_apiUrl = 'https://api.printful.com/';
	protected $_headerArray = array();
	
	function __construct() {
		
		global $lumise;

		// Access core js via your JS function name 
		$this->access_corejs('lumise_addon_printful');

		// Add role for manage component Backgrounds (Wordpress Platform)
		if ($lumise->connector->platform == 'woocommerce') {
			$role = get_role('administrator');
		}
		
		$this->_headerArray = array('Authorization: Bearer '.$lumise->get_option('lumise_printful_api_key').'');

		// create wordpress option data store save
		add_option('lumise_printful_api_key' , '');

		// Insert js addon
		$lumise->add_action('editor-footer', array(&$this, 'editor_footer_js'));

		// lumise option tab
		$lumise->add_action('product-lumise-option-tab', array($this, 'product_lumise_option_tab'));

		// ajax get printful
		add_action('wp_ajax_get_printful', array($this, 'wp_ajax_get_printful'));
		add_action('wp_ajax_detail_printful', array($this, 'wp_ajax_detail_printful'));

		// ajax upload image
		add_action('wp_ajax_upload_printful_img', array($this, 'wp_ajax_upload_printful_img'));

		// get link create
		add_action('wp_ajax_get_post_link', array($this, 'wp_ajax_get_post_link'));

		// filter create temp file design
		$lumise->add_filter('items-cart-temp', array($this, 'items_cart_temp'));		
		
		// filter image name
		$lumise->add_filter('scr-file-name-stage', array($this, 'scr_file_name_stage'));

		// filter custom price
		$lumise->add_filter('add-custom-price-limuse-data', array($this, 'add_custom_price_limuse_data'));	

		// finish order
		$lumise->add_action('store-cart-stage', array($this, 'store_cart_stage'));

	}

	
	public function settings() {
		
		global $lumise;
		
		return array(
			array(
				'type' => 'input',
				'name' => 'lumise_printful_api_key',
				'desc' => '<font color="red">'.$lumise->lang('Document get Printful API').'</font> <a href="https://www.printful.com/docs" target=_blank>'.$lumise->lang('[Read more]').'</a><br/><br/>'.$lumise->lang('To begin using Printful API, follow these steps:').'<br/>'.$lumise->lang('* Go to Settings → Stores').'<br/>'.$lumise->lang('* Select the store you would like to connect by clicking Edit').'<br/>'.$lumise->lang('* Click the “Add API Access” button').'<br/>'.$lumise->lang('* Enter your website URL & get your unique API Key').'<br/>'.'<br><div class="checkKey"></div><a href="https://youtu.be/UVZEFFESg6g" target=_blank>'.$lumise->lang('Watch video for more detail').' ==&gt;</a>'."<script>
(function($) { 

\"use strict\";

$(document).on('keyup', '[name=lumise_printful_api_key]', function(){
	checkFormat($(this).val());
});

$(document).on('paste', '[name=lumise_printful_api_key]', function(){
	checkFormat($(this).val());
});

function checkFormat(data){
	console.log(data);
	var regex = /^(?:[A-Za-z0-9+/]{4})*(?:[A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=)?$/;
	if(regex.test(data) == true){
		$('.checkKey').html('<span style=".'"color: green; font-size: 15px;"'.">".$lumise->lang('Key right format')."!</span>');
		return;
	}
	$('.checkKey').html('<span style=".'"color: red; font-size: 15px;"'.">".$lumise->lang('Key will not work, need base64 encode')."!</span>');
	return;
}

})(jQuery);
 </script>",
				'label' => $lumise->lang('Printful API')
			)
		);
	}

	public function editor_footer_js() {
		
		if(!$this->is_backend()) {
			echo '<script type="text/javascript" src="'.$this->get_url('assets/js/printful_editdesign.js?ver=1').'"></script>';
		}
	}

	public function add_custom_price_limuse_data($lumise_price, $cart_item_data){

		$totalPriceInc = 0;

		$explodeProductID = explode(':', $cart_item_data['product_id']);
		
		if(
			!isset($explodeProductID[1]) 
			|| intval($explodeProductID[1]) == 0 
			|| !isset($cart_item_data['design']['stages'])
		){
			return $lumise_price;
		}

		foreach ($cart_item_data['design']['stages'] as $keyStage => $valueStage) {
			if(isset($valueStage['screenshot']) && isset($valueStage['data']) && isset($valueStage['data']['objects']) && !empty($valueStage['data']['objects']) && isset($valueStage['addon']) && isset($valueStage['addon']['additional_price'])){
				$totalPriceInc += doubleval($valueStage['addon']['additional_price']);
			}
		}

		return $lumise_price+$totalPriceInc;
	}

	public function product_lumise_option_tab(){
		global $lumise;

		$id = get_the_ID();

		echo '<p style="display: none;" class="lumise-button lumise-button-default lumise-button-large" data-func="print_shipping_connection"><i class="fa fa-cubes"></i> '.$lumise->lang('Select product printful').'</p>';

		// css
		$this->editor_header();

		// add js
		$this->editor_footer();
	}

	public function editor_header(){
		if (is_admin()) {
			echo '<link rel="stylesheet" href="'.$this->get_url('assets/css/style.css?ver=1').'" type="text/css" media="all" />';
		}
	}

	public function editor_footer(){
		// add js
		if (is_admin()) {
			// config
				echo "<script>
					var lumise_addon_ship_and_print_connection = {
						url_site: '".$this->get_url('assets/')."',
					};
				</script>";
			// add js
			wp_enqueue_script( 'lumise_addon_connection', $this->get_url('assets/js/script.js?ver=1') );
			wp_localize_script( 'lumise_addon_connection', 'lumise_addon_connection_ajax', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'post_id' => get_the_ID(), 'security' => wp_create_nonce( 'acme-security-nonce' ) ) );
		}
	}

	public function wp_ajax_get_printful(){
		echo $this->getRequest($this->_apiUrl.'products');
		die();
	}

	public function wp_ajax_detail_printful(){
		global $lumise;

		$post_id = intval($_POST['post_id']);
		$printful_id = intval($_POST['printful_id']);

		// detail product
		$connectProduct = $this->getRequest($this->_apiUrl.'products/'.$printful_id);

		$deCodeData = json_decode($connectProduct);

		// check detail product validator
		$this->checkProduct($deCodeData);
		
		$mappingArr = $attributeData = $attributeArr = $resultEcho = $listImage = $variantData = $sizeProduct = $colorProduct = $tempcolorProduct = $tempsizeProduct = array();
		$default_variant_id = intval($deCodeData->result->variants[0]->id);
		$product_options = isset($deCodeData->result->product->options) ? $deCodeData->result->product->options : [];

		// get list size & color Product and format data
		foreach ($deCodeData->result->variants as $indexVari => $valueVari) {
			// add size to array if not exist
			if(in_array($valueVari->size, $tempsizeProduct) == FALSE && $valueVari->size != null){
				array_push($tempsizeProduct, $valueVari->size);
			}

			// add color to array if not exist
			if(!isset($tempcolorProduct[$valueVari->color_code]) && ($valueVari->color != null || $valueVari->color_code != null)){
				$tempcolorProduct[$valueVari->color_code] = $valueVari->color;
			}

			// make data Arr
			if(!isset($variantData[$valueVari->id])){
				$variantData[$valueVari->id] = $valueVari;
				$variantData[$valueVari->id]->variant_id = $variantData[$valueVari->id]->id;
				$variantData[$valueVari->id]->files = $deCodeData->result->product->files;

				unset($variantData[$valueVari->id]->id);
			}

			// add img to list if not exist
			if(!isset($listImage[$valueVari->image]) && $valueVari->image != null){
				$listImage[$valueVari->image] = array('image' => $valueVari->image);
			}
		}

		// format data
		foreach ($tempsizeProduct as $keyTempSizeP => $valueTempSizeP) {
			array_push($sizeProduct, array('title' =>$valueTempSizeP, 'value' => $valueTempSizeP));
			$attributeData['size'][] = $valueTempSizeP;
		}

		// format data
		foreach ($tempcolorProduct as $keyTempColorP => $valueTempColorP) {
			array_push($colorProduct, array('value' =>$keyTempColorP, 'title' => $valueTempColorP));
			$attributeData['color'][] = $valueTempColorP;
		}

		// save data
		$attributeArr['size'] = $sizeProduct;
		$attributeArr['color'] = $colorProduct;

		if(!empty($attributeData['size'])){
			$attributeData['size'] = array(
				'id' => 0,
				'name' => 'size',
				'options' => $attributeData['size'],
				'position' => 0,
				'visible' => true,
				'variation' => true,
				'is_visible' => 1,
				'is_variation' => 1,
				'is_taxonomy' => 0,
				'value' => implode(" | ", $attributeData['size'])
			);
		}
		if(!empty($attributeData['color'])){
			$attributeData['color'] = array(
				'id' => 0,
				'name' => 'color',
				'options' => $attributeData['color'],
				'position' => 0,
				'visible' => true,
				'variation' => true,
				'is_visible' => 1,
				'is_variation' => 1,
				'is_taxonomy' => 0,
				'value' => implode(" | ", $attributeData['color'])
			);
		}
		
		// get template 
		$templateProduct = $this->getRequest($this->_apiUrl.'mockup-generator/templates/'.intval($printful_id));
		$deCodeData = json_decode($templateProduct);

		// get print file information 
		$printfileProduct = $this->getRequest($this->_apiUrl.'mockup-generator/printfiles/'.intval($printful_id));
		$deCodeDataPrintfile = json_decode($printfileProduct);
		if(isset($deCodeDataPrintfile->result->printfiles)){
			$resortArr = array();
			foreach ($deCodeDataPrintfile->result->printfiles as $key => $value) {
				$resortArr[intval($value->printfile_id)] = $value;
			}
			$resultEcho['printfile'] = $resortArr;
		}

		// validator data
		$this->checkVariantTemplate($deCodeData);

		// create full data (variantData) and image filter for download optimation
		foreach ($deCodeData->result->variant_mapping as $key => $detailMapping) {
			if(isset($variantData[$detailMapping->variant_id]) && !empty($variantData[$detailMapping->variant_id])){
				foreach ($detailMapping->templates as $keyTemplate => $valueTemplate) {
					foreach ($deCodeData->result->templates as $keyTemp => $valueTemp) {
						if ($valueTemplate->template_id == $valueTemp->template_id) {
							$variantData[$detailMapping->variant_id]->template_ids[$valueTemplate->template_id] = $valueTemp;
						}
					}
					$variantData[$detailMapping->variant_id]->template_ids[$valueTemplate->template_id]->placement = $valueTemplate->placement;
				}
			}
		}
		
		foreach ($deCodeData->result->templates as $key => $detailTemplate) {
			if(!isset($listImage[$detailTemplate->image_url])){
				$listImage[$detailTemplate->image_url] = array('image' => $detailTemplate->image_url);
			}
			foreach ($variantData as $indexVarianData => $valueVarianData) {
				if (isset($variantData->template_ids[$detailTemplate->template_id])) {
					$variantData->template_ids[$detailTemplate->template_id]->detail = $detailTemplate;
				}
			}
		}
		
		$resultEcho['img'] = $listImage;
		$resultEcho['fulldata'] =  $variantData;
		$resultEcho['attribute'] =  $attributeData;

		// create this post to variable product
		$product = wc_get_product($post_id);
		wp_remove_object_terms( $post_id, $product->get_type(), 'product_type' );
   		wp_set_object_terms( $post_id, 'variable', 'product_type', true );

		// create attribute
		$createAttribute = $this->createAttribute($post_id, $attributeArr);
		// create options printfull                                     
		$createOption = update_post_meta( $post_id, '_product_options', json_decode(json_encode($product_options), true));
		echo json_encode(array('code' => 200, 'message' => $resultEcho));
		die();
	}

	public function wp_ajax_upload_printful_img(){
		global $lumise;

		if (!isset($_REQUEST['link']) ) {
			echo json_encode(array('code' => 0, 'message' => $lumise->lang('Missing image link') ));
			die();
		}

		// download image optimation filter
		$imgData = array();
		$fileUpload = $this->uploadImg($_REQUEST['link']);
		$imgData['imgName'] = $fileUpload['name'];
		$imgData['uploadImg'] = 'products/'.$fileUpload['name'];
		$imgData['uploadfullPath'] = $lumise->cfg->upload_url.'products/'.$fileUpload['name'];
		$imgData['file_id'] = $fileUpload['file_id'];

		echo json_encode(array('status' => 1, 'message' => $imgData));
		die();
	}

	public function wp_ajax_get_post_link(){
		if (!isset($_REQUEST['post_id']) ) {
			echo json_encode(array('code' => 0, 'message' => 'Missing post id'));
			die();
		}

		$link = get_edit_post_link(intval($_REQUEST['post_id']), '');

		echo json_encode(array('status' => 1, 'message' => $link));
		die();
	}

	public function createAttribute($post_id, $attributeData){
		$attribute_data = array();

		foreach ($attributeData as $indexDetail => $valueDetail) {
			$attribute_data[$indexDetail] = array(
				'name' => $indexDetail,
				'position' => '0',
				'is_visible' => '0',
				'is_variation' => '1',
				'is_taxonomy' => '0'
			);
			$valueArr = '';
			foreach ($valueDetail as $keyValueDetail => $valueValueDetail) {
				$valueArr .= $valueValueDetail['title'].' | ';
			}
			if ($valueArr != '') {
				$attribute_data[$indexDetail]['value'] = substr($valueArr, 0, -3);
			}
		}
		$postMeta = update_post_meta( $post_id, '_product_attributes', $attribute_data );

		return $postMeta;
	}

	public function items_cart_temp($items_cart_data, $itemData){
		global $lumise;

		if (isset($items_cart_data['design']->stages) && !empty($items_cart_data['design']->stages)) {
			foreach ($items_cart_data['design']->stages as $indexStage => $valueStage) {
				foreach ($itemData['product_stages'] as $stage_id => $stage_data) {
					if( $indexStage == $stage_id && isset($stage_data->addon) ){
						$items_cart_data['design']->stages->$indexStage->addon = $stage_data->addon;
						break;
					}
				}
			}
		}

		return $items_cart_data;
	}

	public function scr_file_name_stage($scr_file_name, $data){
		global $lumise;

		if(isset($data['sdata']['printful']['placement'])){
			$time = time();

			$scr_file_name = date('Y', $time).DS.date('m', $time).DS.$data['sdata']['printful']['placement'].'_printful-'.$lumise->generate_id().'-stage'.$data['sdata']['isf'].'.png';

			return $scr_file_name;
		}

		return $scr_file_name;
	}

	protected function getRequestImage($urlRequest = ''){
		global $lumise;

		$ch = curl_init($urlRequest);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		if ($result == FALSE || $result == NULL || curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
			if($result != '' || $result != NULL || $result != FALSE){
				if(isset(json_decode($result)->code) && isset(json_decode($result)->result)){
					echo json_encode(array('code' => 0, 'message' => json_decode($result)->result));
					die();
				}
			}
			echo json_encode(array('code' => 0, 'message' => $lumise->lang('getRequestImage - connect to printful error, plz try again after some minutes!' . json_encode($result))));
			die();
        }
		curl_close($ch);
		return $result;
	}

	protected function getRequest($urlRequest = ''){
		global $lumise;
		
		if(empty($this->_headerArray)){
			echo json_encode(array('code' => 0, 'message' => $lumise->lang('Missing Printful API Key')));
			die();
		}

		$ch = curl_init($urlRequest);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_headerArray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// print_r($this->_headerArray);
		$result = curl_exec($ch);
		if ($result == FALSE || $result == NULL || curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
			if($result != '' || $result != NULL || $result != FALSE){
				if(isset(json_decode($result)->code) && isset(json_decode($result)->result)){
					echo json_encode(array('code' => 0, 'message' => json_decode($result)->result));
					die();
				}
			}
			echo json_encode(array('code' => 0, 'message' => $lumise->lang('getRequest - connect to printful error, plz try again after some minutes!' . json_encode($result))));
			die();
        }
		curl_close($ch);
		return $result;
	}

	protected function postRequest($urlRequest, $data) {
		global $lumise;

		if(empty($this->_headerArray)){
			echo json_encode(array('code' => 0, 'message' => $lumise->lang('Missing Printful API Key')));
			die();
		}
		
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlRequest);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_headerArray);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $result = curl_exec($ch);
        if ($result == FALSE || $result == NULL || curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
            echo json_encode(array('code' => 0, 'message' => $lumise->lang('postRequest - connect to printful error, plz try again after some minutes!' . json_encode($result))));
			die();
        }

        $result = json_decode($result, true);
        return $result;
	}

	protected function checkProduct($deCodeData){
		global $lumise;

		// check code response
		if(!isset($deCodeData->code) || intval($deCodeData->code) != 200 ){
			echo json_encode(array('code' => 0, 'message' => $lumise->lang('response data wrong format!')));
			die();
		}

		// check variants
		if(!isset($deCodeData->result->product->currency)){
			echo json_encode(array('code' => 0, 'message' => $lumise->lang('cant find currency')));
			die();
		}

		// check currency
		if(!isset($deCodeData->result->product->currency)){
			echo json_encode(array('code' => 0, 'message' => $lumise->lang('cant find currency on printful')));
			die();
		}

		if($deCodeData->result->product->currency != get_woocommerce_currency()){
			echo json_encode(array('code' => 0, 'message' => $lumise->lang('Your store wordpress not same currency with your store printful!')));
			die();
		}

		// check variants
		if(!isset($deCodeData->result->variants[0]) || empty($deCodeData->result->variants[0])){
			echo json_encode(array('code' => 0, 'message' => $lumise->lang('cant find variants')));
			die();
		}
	}

	protected function checkVariantTemplate($deCodeData){
		global $lumise;
		
		// check code response
		if(!isset($deCodeData->code) || intval($deCodeData->code) != 200 ){
			echo json_encode(array('code' => 0, 'message' => $lumise->lang('response data wrong format!')));
			die();
		}

		// empty variant mapping
		if(!isset($deCodeData->result->variant_mapping) || empty($deCodeData->result->variant_mapping)){
			echo json_encode(array('code' => 0, 'message' => $lumise->lang('cant find variants mapping')));
			die();
		}

		if(!isset($deCodeData->result->templates) || empty($deCodeData->result->templates)){
			echo json_encode(array('code' => 0, 'message' => $lumise->lang('empty template!')));
			die();
		}
	}
	
	protected function uploadImg($url){
		global $lumise;
		
		$fileUpload = array();
		$data = file_get_contents($url);

		// //get name, path image
		$randomName = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10).date('Ymd-His');
		$filename = $randomName.'.png';

		// empty file image connect
		if(!isset($data) || $data == null || $data == ''){
			echo json_encode(array('status' => 0, 'message' => $lumise->lang('Create image fail!') ));
			die();
			return $filename;
		}

		if(is_dir( $lumise->cfg->upload_path.'products') == false){
			echo json_encode(array('status' => 0, 'message' => $lumise->lang('Missing products folder in lumise_data') ));
			die();
		}

		$fileUpload['name'] = $filename;
		$filePath = $lumise->cfg->upload_path.'products/'.$filename;
		$uploadPath = $lumise->cfg->upload_url.'products/'.$filename;
		$pushData = file_put_contents($filePath, $data);

		$upload_id = wp_insert_attachment( array(
			'guid'           => $uploadPath, 
			'post_mime_type' => 'image/png',
			'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		), $uploadPath );

		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		wp_update_attachment_metadata( $upload_id, wp_generate_attachment_metadata( $upload_id, $uploadPath ) );

		$fileUpload['file_id'] = $upload_id;

		return $fileUpload;
	}

	public function store_cart_stage($order_id, $dataArr){
		global $lumise;
		
		$order_id = (Int)$order_id;
		// $order_id = 473;
		$order = wc_get_order( $order_id );
		$customerName = $order->get_formatted_billing_full_name();
		$customerAddress = $order->get_billing_address_1();
		if($order->get_billing_address_1() == '' && $order->get_billing_address_2() != ''){
			$customerAddress = $order->get_billing_address_2();
		}
		$customerCity = $order->get_billing_city();
		$customerStateCode = $order->get_billing_state();
		$customerCountryCode = $order->get_billing_country();
		$customerZip = $order->get_billing_postcode();

		// exist shipping get shipping 
		if(
			$order->get_formatted_shipping_full_name() != '' 
			&& ($order->get_shipping_address_1() != '' || $order->get_shipping_address_2() != '') 
			&& $order->get_shipping_city() != ''
		){
			$customerName = $order->get_formatted_shipping_full_name();
			$customerAddress = $order->get_shipping_address_1();
			if($order->get_shipping_address_1() == '' && $order->get_shipping_address_2() != ''){
				$customerAddress = $order->get_shipping_address_2();
			}
			$customerCity = $order->get_shipping_city();
			$customerStateCode = $order->get_shipping_state();
			$customerCountryCode = $order->get_shipping_country();
			$customerZip = $order->get_shipping_postcode();
		}

		// get file data
		$productQly = 0;
		$productVariation = '';
		$productVariationID = 0;

		if ($order_id == 0) {
			echo json_encode(array('status' => 0, 'message' => $lumise->lang('wrong order id') ));
			die();
			return;
		}

		$productQly = intval($dataArr['qty']);

		// get print file
		$printful = false;
		$listPrintFile = array();

		foreach ($dataArr['stagesArr'] as $indexStage => $valueStage) {
			if (isset($valueStage['addon']) && $valueStage['addon'] != null && isset($valueStage['addon']['type']) && $valueStage['addon']['type'] == 'printful' && isset($valueStage['limuse_print_file']) && $valueStage['limuse_print_file'] != null) {
				$existprintful = true;
				$productVariationID = intval($valueStage['addon']['variant_id']);

				$filename = str_replace('\\', '/', $valueStage['limuse_print_file']);
				if(isset($valueStage['data']['objects']) && !empty($valueStage['data']['objects'])){
					// array_push($listPrintFile, array('type' => $valueStage['addon']['placement'], 'url' => $lumise->cfg->upload_url.'orders/'.$filename));

					if($valueStage['addon']['placement'] == 'label_inside'){
						array_push($listPrintFile, array(
							'type' => $valueStage['addon']['placement'], 
							'url' => $lumise->cfg->upload_url.'orders/'.$filename, 
							'options' => [
								array('id' => 'template_type', 'value' => 'native')
							]
						));
					} else {
						array_push($listPrintFile, array('type' => $valueStage['addon']['placement'], 'url' => $lumise->cfg->upload_url.'orders/'.$filename));
					}
					
				}
			}
		}

		if ($existprintful == true && $productVariationID != 0) {
			$options = [];
			$product_options = get_post_meta(intval($dataArr['product_cms']),'_product_options',true);
			$product_options = json_decode(json_encode($product_options), true);
			$option_ids = wp_list_pluck( $product_options, 'id' );
			if(in_array('stitch_color', $option_ids)){
				$key = array_search( 'stitch_color', $option_ids );
				$options[] = array(
					'id' => "stitch_color",
					'value' => key($product_options[$key]['values'])
				);
			}
			$dataSent = array(
				'recipient' => array(
					"name" => $customerName,
					"address1" => $customerAddress,
					"city" => $customerCity,
					"state_code" => $customerStateCode,
					"country_code" => $customerCountryCode,
					"zip" => $customerZip
				),
				"items" => array(
					array(
						"variant_id" => $productVariationID,
						"quantity" => intval($productQly),
						"files" => $listPrintFile,
						"options" => $options
					)
				)
			);
			
			// post to printful
			$connectProduct = $this->postRequest($this->_apiUrl.'orders', json_encode($dataSent));

			if($connectProduct['code'] != 200){
				echo json_encode(array('code' => 0, 'message' => $lumise->lang('request to printful error')));
				die();
			}
		}
	}

	
	/*
		Actions on active or deactive this addon
	*/
	static function active() {
		
		global $lumise;
		
		$columns = $lumise->db->rawQuery("SHOW COLUMNS FROM `{$lumise->db->prefix}products` LIKE 'ship_print_connect_data'");
		
		if(count($columns) === 0){
			$lumise->db->rawQuery(
				"ALTER TABLE `{$lumise->db->prefix}products` ADD `ship_print_connect_data` TEXT NOT NULL DEFAULT '' AFTER `author`"
			);
		}
		
	}
	
	static function deactive() {
		global $lumise;
		
	}
	
}