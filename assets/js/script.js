(function($) {
	
	"use strict";
	
	$('body form[name="post"]').append(modalContent());
	
	function modalContent() {
	    return '<div id="modal_addon_lumise_shipping_print" class="modal_addon_lumise_shipping_print"> <div class="modal_content_addon_lumise_shipping_print"> <div class="modal_header_addon_lumise_shipping_print"> <span class="close">&times;</span> <h2>Product Printful</h2> </div><div class="modal-body">'+loading()+'</div></div></div>';
	}
	
	function loading() {
	    return '<div class="center_modal" style="padding: 15px 0px;"><div><img style="width: 100px;" src="'+lumise_addon_ship_and_print_connection.url_site+'images/loading.gif"></div><div class="info_loading">Connect to Printful, It can be take some minutes</div></div>';
	}
	
	function success(){
	    var push_update = $('body form[name="post"] div#submitpost div#major-publishing-actions div#publishing-action').html();
	    return '<div class="center_modal" style="padding: 15px 0px;"><div class="info_loading">Push or Update to save change printful product</div><div>'+push_update+'</div></div>';
	}
	
	var listImg = null;
	var listFullData = null;
	var attribute = null;
	var imgProcessIndex = 0;
	var createVariationIndex = 0;
	var imgProcessList = [];
	var createVariationProcess = [];
	
	$(document).on('click', '[data-func="print_shipping_connection"]', function(){
	    $('div.modal_addon_lumise_shipping_print div.modal-body').html(loading());
	    $('.modal_addon_lumise_shipping_print').show();
	
	    $.ajax({
	        url: lumise_addon_connection_ajax.ajax_url,
	        dataType: "json",
	        type: 'POST',
	        data: {
	           action: 'get_printful',
	        },
	        success: function(result) {
	            if ( result.code != 200 && result.code != 1 && result.message == '') {
	                $('div.modal-body').html('Connect to printful error, plz try again!');
	                return;
	            }
	            if ( result.code != 200 && result.code != 1 && result.message != '') {
	                $('div.modal-body').html(result.message);
	                return;
	            }
	            let htmlRender = '<ul class="lumise-stagle-list-base" id="lumise-sample-bases">';
	            htmlRender += '<p>For now, we only support digital printing, Embroidery will support later</p>';
	            $.each(result.result, function(index, value){
	            	// console.log(index, value.id);
	                if(value.image == null || value.type == 'EMBROIDERY' || value.type == 'STOCK-PRODUCT'){
	                    return;
	                }
	                // console.log(value.type, '| '+value.model);
	                htmlRender += '<li data-id="'+value.id+'" data-act="printful_product"><img src="'+value.image+'"><span>'+value.model+'</span></li>';
	            });
	            htmlRender += '</ul>';
	            $('div.modal-body').html(htmlRender);
	        }
	    });
	});
	
	
	$(document).on('click', 'div.modal_header_addon_lumise_shipping_print .close', function(){
	    $('.modal_addon_lumise_shipping_print').hide();
	});
	
	
	$(document).on('click', '[data-act="printful_product"]', function(){
	
	    $('div.modal_content_addon_lumise_shipping_print div.modal-body').html(loading());
	
	    listImg = null;
	    listFullData = null;
	    attribute = null;
	    imgProcessIndex = 0;
	    createVariationIndex = 0;
	    imgProcessList = [];
	    createVariationProcess = [];
	
	    $.ajax({
	        url: lumise_addon_connection_ajax.ajax_url,
	        dataType: "json",
	        type: 'POST',
	        data: {
	           action: 'detail_printful',
	           post_id: lumise_addon_connection_ajax.post_id,
	           printful_id: $(this).attr('data-id')
	        }
	        // success: function(result) {
	        //     if (result.code == 0) {
	        //         $('div.modal-body').html(result.message);
	        //         return false;
	        //     }
	        //     if (result.code == 200) {
	        //         let doneUpload = false;
	        //         var i = 0;
	        //         var u = 0;
	        //         let lengthOfImg = Object.keys(result.message.img).length;
	        //         $('.info_loading').html('<span class="process_image">Process Image from printful</span> (<span class="process_image_from">0</span>/<span class="process_image_to">'+lengthOfImg+'</span>)');
	        //         if (result.message.attribute) {
	        //             $.each(result.message.attribute, function(index, value){
	        //                 createAttribute(u, index, value);
	        //                 u++;
	        //             });
	        //             attribute = result.message.attribute;
	        //         };
	        //         if (result.message.img) {
	        //             listImg = result.message.img;
	        //             listFullData = result.message.fulldata;

	        //             // uploadImage(value.image, lengthOfImg, result.message.printfile);

	        //             $.each(result.message.img, function(index, value){
	        //             	imgProcessList.push(value.image); 
	        //             });
	        //             uploadImage(imgProcessList[imgProcessIndex], lengthOfImg, result.message.printfile);
	        //         };
	        //     }
	        // }
	    }).done(function(result){
	    	if (result.code == 0) {
                $('div.modal-body').html(result.message);
                return false;
            }
            if (result.code == 200) {
                let doneUpload = false;
                var i = 0;
                var u = 0;
                var lengthOfImg = Object.keys(result.message.img).length;
                if (result.message.attribute) {
                    $.each(result.message.attribute, function(index, value){
                        createAttribute(u, index, value);
                        u++;
                    });
                    attribute = result.message.attribute;
                };
                if (result.message.img) {
                    listImg = result.message.img;
                    listFullData = result.message.fulldata;

                    // uploadImage(value.image, lengthOfImg, result.message.printfile);

                    $.each(result.message.img, function(index, value){
                    	imgProcessList.push(value.image); 
                    });

                    // $('div.modal_content_addon_lumise_shipping_print div.modal-body').html('<div class="center_modal" style="padding: 15px 0px;"><div><img style="width: 100px;" src="'+lumise_addon_ship_and_print_connection.url_site+'images/loading.gif"></div><span class="process_image">Process Image from printful</span> (<span class="process_image_from">0</span>/<span class="process_image_to">'+lengthOfImg+'</span>)</div>');
                    
                    // $('.info_loading').html('');
                    $('.info_loading').html('<span class="process_image">Process Image from printful</span> (<span class="process_image_from">0</span>/<span class="process_image_to">'+lengthOfImg+'</span>)');
                    uploadImage(imgProcessList[imgProcessIndex], lengthOfImg, result.message.printfile);
                };
            }
	    });
	});
	
	function createAttribute(i, name, values){
	
	    $('div.product_attributes').append('<div data-taxonomy="" class="woocommerce_attribute wc-metabox closed " rel="0"><h3><a href="#" class="remove_row delete">Remove</a><div class="handlediv" title="Click to toggle"></div><div class="tips sort" data-tip="Drag and drop to set admin attribute order"></div><strong class="attribute_name">'+values.name+'</strong></h3><div class="woocommerce_attribute_data wc-metabox-content hidden"><table cellpadding="0" cellspacing="0"><tbody><tr><td class="attribute_name"><label>Name:</label><input type="text" class="attribute_name" name="attribute_names['+i+']" value="'+values.name+'"><input type="hidden" name="attribute_position['+i+']" class="attribute_position" value="0"></td><td rowspan="3"><label>Value(s):</label><textarea name="attribute_values['+i+']" cols="5" rows="5" placeholder="Enter some text, or some attributes by &quot;|&quot; separating values.">'+values.value+'</textarea></td></tr><tr><td><label><input type="checkbox" class="checkbox" checked="checked" name="attribute_visibility['+i+']" value="1"> Visible on the product page</label></td></tr><tr><td><div class="enable_variation show_if_variable" style="display: block;"><label><input type="checkbox" class="checkbox" checked="checked" name="attribute_variation['+i+']" value="1"> Used for variations</label></div></td></tr></tbody></table></div></div>');
	}
	
	function loadAttribute(){
		
	    var page     = 1;
	    var per_page = 15;
	
	    var wrapper = $( '#variable_product_options' ).find( '.woocommerce_variations' );
	
	    $.ajax({
	        url: woocommerce_admin_meta_boxes_variations.ajax_url,
	        data: {
	            action:     'woocommerce_load_variations',
	            security:   woocommerce_admin_meta_boxes_variations.load_variations_nonce,
	            product_id: woocommerce_admin_meta_boxes_variations.post_id,
	            attributes: attribute,
	            page:       page,
	            per_page:   per_page
	        },
	        type: 'POST',
	        success: function( response ) {
		        
	            wrapper.empty().append( response ).attr( 'data-page', page );
	
	            $( '#woocommerce-product-data' ).trigger( 'woocommerce_variations_loaded' );
	
	            $('div.modal_addon_lumise_shipping_print div.modal-body').html(success());
	            
	        }
	    });
	}
	
	function uploadImage(imgLink = '', lengthOfImg = 0, printfile){
		// $('div.modal_addon_lumise_shipping_print div.modal-body').html('sdfgdfhfdhdfhdf');
		imgProcessIndex++;
        $('.process_image .process_image_from').html(imgProcessIndex);

	    $.ajax({
	        url: lumise_addon_connection_ajax.ajax_url,
	        dataType: "json",
	        type: 'POST',
	        // async: false,
	        data: {
	           action: 'upload_printful_img',
	           link: imgLink
	        },
	        success: function(result) {
	            if (result.status == 0) {
	                $('div.modal-body').html(result.message);
	                return false;
	            }
	            if (result.status == 1) {
	                // count image upload success
	                $('.process_image_from').html(imgProcessIndex);
	
	                // listImg.imgLink.upload = result.message;
	                listImg[imgLink].upload = result.message;
	
	                if(imgProcessIndex == lengthOfImg){
	                    // create variation
	                    comparImgWithData();
	                    // var i = 0;
	                    var lengthOgData = Object.keys(listFullData).length;;
	                    $('.info_loading').html('<span class="process_variation">Create variation</span> (<span class="process_variation_from">0</span>/<span class="process_variation_to">'+lengthOgData+'</span>)');
	                    // $.each(listFullData, function(index, value){
	                    //     i++;
	                    //     createVariation(i, value, printfile);
	                    // });
	                    $.each(listFullData, function(index, value){
	                    	createVariationProcess.push(value);
	                    });

	                    createVariation(createVariationIndex, createVariationProcess[createVariationIndex], printfile);
	                    return;
	                }
	                setTimeout(function(){ 
	                	uploadImage(imgProcessList[imgProcessIndex], lengthOfImg, printfile);
				    }, 500); 
	            }
	            
	        }
	    });
	}
	
	function comparImgWithData(){
	    $.each(listFullData, function(index, value){
	
	        // chay vong lap lay anh feature
	        $.each(listImg, function(index2, value2){
	            if(value.image == value2.image){
	                listFullData[index].imgName = value2.upload.imgName;
	                listFullData[index].uploadImg = value2.upload.uploadImg;
	                listFullData[index].uploadfullPath = value2.upload.uploadfullPath;
	                listFullData[index].file_id = value2.upload.file_id;
	            }
	        });
	
	        // chay vong lap lay anh trong template
	        $.each(value.template_ids, function(index3, value3){
	            $.each(listImg, function(index4, value4){
	                if(value3.image_url == value4.image){
	                    listFullData[index].template_ids[index3].imgName = value4.upload.imgName;
	                    listFullData[index].template_ids[index3].uploadImg = value4.upload.uploadImg;
	                    listFullData[index].template_ids[index3].uploadfullPath = value4.upload.uploadfullPath;
	                    listFullData[index].template_ids[index3].file_id = value4.upload.file_id;
	                }
	            });
	            
	        });
	    });
	    
	}
	
	function createVariation(indexData, dataValue, printfile){

	    var data = {
	        action: 'woocommerce_add_variation',
	        post_id: woocommerce_admin_meta_boxes_variations.post_id,
	        loop: indexData,
	        security: woocommerce_admin_meta_boxes_variations.add_variation_nonce
	    };
	
	    $.post( woocommerce_admin_meta_boxes_variations.ajax_url, data, function( response ) {
	        var variation = $( response );
	        variation.find('select[name="attribute_color['+indexData+']"]').val(dataValue.color);
	        variation.find('select[name="attribute_size['+indexData+']"]').val(dataValue.size);
	        variation.find('input[name="upload_image_id['+indexData+']"]').val(dataValue.file_id);
	
	        // price
	        variation.find('input[name="variable_regular_price['+indexData+']"]').val(dataValue.price);
	
	        var stageVariation = {};
	        var stageBase = {
	            printing: encodeURIComponent(JSON.stringify({}))
	        };
	
	        $.each(dataValue.template_ids, function(indexDetailVariation, valueDetailVariation){
	            if(valueDetailVariation.placement == 'label_outside'){
	                return;
	            }
	            var idNStage = randomStr(8);
	            stageVariation[idNStage] = createStageVariation(dataValue, valueDetailVariation, dataValue.files, printfile);
	        });
	        stageBase.stages = btoa(encodeURIComponent(JSON.stringify(stageVariation)));
	        // variation.find('textarea[name="variable_lumise['+indexData+']"]').val(btoa(encodeURIComponent(JSON.stringify(stageBase))));
	        variation.find('textarea[name="variable_lumise['+indexData+']"]').val(encodeURIComponent(JSON.stringify(stageBase)));
	
	        saveVariation(variation, printfile);
	    });
	}
	
	function createStageVariation(stageInfor, templateDetail, files, printfile){
	    // edz calc & base
	    var edzLeft = templateDetail.print_area_left, edzLeftCss = templateDetail.print_area_left;
	    var edzTop = templateDetail.print_area_top, edzTopCss = templateDetail.print_area_top;
	    var edzWidth = templateDetail.print_area_width, edzWidthCss = templateDetail.print_area_width;
	    var edzHeigh = templateDetail.print_area_height, edzHeighCss = templateDetail.print_area_height;
	
	    var imgWidth = templateDetail.template_width;
	    var imgHeigh = templateDetail.template_height;
	
	    var colorBg = '';
	    if(templateDetail.background_color){
	        colorBg = templateDetail.background_color;
	    }
	
	    var additional_price = null;
	    $.each(files, function(index, value){
	        if(templateDetail.placement == value.type){
	            additional_price = value.additional_price;
	            return false;
	        }
	    });
	
	    if(imgWidth > 400){
	        let pointRezise = parseFloat(imgWidth)/400;
	        edzWidthCss = (parseFloat(edzWidthCss)/pointRezise);
	        edzHeighCss = (parseFloat(edzHeighCss)/pointRezise);
	        edzLeftCss = (parseFloat(edzLeftCss)/pointRezise);
	        edzTopCss = (parseFloat(edzTopCss)/pointRezise);
	
	        // console.log('edzLeftCss :',edzLeftCss);
	        // console.log('edzTopCss :', edzTopCss);
	
	        var imgWidthCss = 400;
	        var imgHeightCss = (parseFloat(imgHeigh)/pointRezise);
	
	        edzLeftCss = Math.round((edzLeft-(imgWidth/2)+(edzWidth/2))*100)/100;
	        edzTopCss = Math.round((edzTop-(imgHeigh/2)+(edzHeigh/2))*100)/100;
	
	    }
	    var stageVariation = {
	        edit_zone: {
	          height: edzHeigh,
	          width: edzWidth,
	          left: edzLeftCss,
	          top: edzTopCss,
	          radius: '0'
	        },
	        url: templateDetail.uploadImg,
	        source: 'addon',
	        overlay: false,
	
	        hide_size: true,
	        hide_edz: true,
	        hide_mark_layer: true,
	        hide_select_clear: true,
	
	        product_width: imgWidth,
	        product_height: imgHeigh,
	
	        template: {},
	        size: {
	            width: ''+printfile[templateDetail.printfile_id].width+'',
	            height: ''+printfile[templateDetail.printfile_id].height+'',
	            constrain: true,
	            unit: "px"
	        },
	        addon: {
	            type: 'printful',
	            height: edzHeigh,
	            width: edzWidth,
	            left: edzLeft,
	            top: edzTop,
	            imgWidth: imgWidth,
	            imgHeigh: imgHeigh,
	            product_id: stageInfor.product_id,
	            price: stageInfor.price,
	            in_stock: stageInfor.in_stock,
	            variant_id: stageInfor.variant_id,
	            file_id: stageInfor.file_id,
	            template_id: templateDetail.template_id,
	            image_url: templateDetail.image_url,
	            printfile_id: templateDetail.printfile_id,
	            placement: templateDetail.placement,
	            imgName: templateDetail.imgName,
	            uploadImg: templateDetail.uploadImg,
	            uploadfullPath: templateDetail.uploadfullPath,
	            additional_price: additional_price
	        },
	        crop_marks_bleed: false,
	        bleed_range: '',
	        orientation: 'portrait',
	        label: templateDetail.placement,
	        color: colorBg
	    };
	
	    return stageVariation;
	}
	
	function saveVariation(need_update, printfile){
	    $( '#variable_product_options' ).trigger( 'woocommerce_variations_save_variations_button' );
	
	    var wrapper     = $( '#variable_product_options' ).find( '.woocommerce_variations' ),
	        data        = {};
	
	    data                 = get_variations_fields( need_update );
	    data.action          = 'woocommerce_save_variations';
	    data.security        = woocommerce_admin_meta_boxes_variations.save_variations_nonce;
	    data.product_id      = woocommerce_admin_meta_boxes_variations.post_id;
	    data['product-type'] = $( '#product-type' ).val();
	
	    $.ajax({
	        url: woocommerce_admin_meta_boxes_variations.ajax_url,
	        data: data,
	        type: 'POST',
	        success: function( response ) {
	
	            let count = parseInt($('.process_variation_from').html())+1;
	            $('.process_variation_from').html(count);
	            let maxOfLength = parseInt($('.process_variation_to').html());
	
	            // Allow change page, delete and add new variations
	            need_update.removeClass( 'variation-needs-update' );
	            $( 'button.cancel-variation-changes, button.save-variation-changes' ).attr( 'disabled', 'disabled' );
	
	            $( '#woocommerce-product-data' ).trigger( 'woocommerce_variations_saved' );
	
	            if ( typeof callback === 'function' ) {
	                callback( response );
	            }
	
	            if(count == maxOfLength){
	                loadAttribute();
	                return;
	            }

	            createVariationIndex++;
	            setTimeout(function(){ 
                	createVariation(createVariationIndex, createVariationProcess[createVariationIndex], printfile);
			    }, 500); 

	        }
	    });
	}
	
	function getNewLink(){
	    $.ajax({
	        url: lumise_addon_connection_ajax.ajax_url,
	        dataType: "json",
	        type: 'POST',
	        data: {
	           action: 'get_post_link',
	           post_id: woocommerce_admin_meta_boxes_variations.post_id
	        },
	        success: function(result) {
	            if (result.status == 0) {
	                $('div.modal-body').html(result.message);
	                return false;
	            }
	            if (result.status == 1) {
	            }
	            
	        }
	    });
	}
	
	function getAllData(urlPara){
	    $.ajax({
	        url: urlPara,
	        dataType: "json",
	        type: 'GET',
	        success: function(result) {
	            console.log('success load');
	            
	        }
	    });
	}
	
	function get_variations_fields( fields ) {
	    var data = $( ':input', fields ).serializeJSON();
	
	    $( '.variations-defaults select' ).each( function( index, element ) {
	        var select = $( element );
	        data[ select.attr( 'name' ) ] = select.val();
	    });
	    return data;
	}
	
	function randomStr(length) {
	    var result           = '';
	    var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	    var charactersLength = characters.length;
	    for ( var i = 0; i < length; i++ ) {
	       result += characters.charAt(Math.floor(Math.random() * charactersLength));
	    }
	    return result.toUpperCase();
	 }
	
	
	showBtn();
	$('select#product-type[name="product-type"]').change(function(){
	    showBtn();
	});
	
	function showBtn(){
	    if($('select#product-type[name="product-type"]').val() == 'variable'){
	        $('[data-func="print_shipping_connection"]').show();
	    } else {
	        $('[data-func="print_shipping_connection"]').hide();
	    }
	}

})(jQuery);