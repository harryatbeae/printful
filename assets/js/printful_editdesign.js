function lumise_addon_printful(lumise) {

	window.lm = lumise;

	var stage_arr = {};

	lumise.add_action('first-completed', function(){
		if(lumise.fn.url_var('cart', '') != '' && lumise.data != undefined && lumise.data.stages != undefined){
			$.each(lumise.data.stages, function(index, detailStage){
				if(detailStage.addon != undefined && detailStage.addon.type == 'printful'){
					stage_arr[index] = detailStage.addon;
				}
			})
			$('.lumise-cart-attributes select').attr('disabled', true);
		}
	});

	// lumise.add_action('load_object_stages', function(obj){
	// 	if(
	// 		// printful addon price stage
	// 		(
	// 			stage_arr[lumise.current_stage] != undefined 
	// 			&& stage_arr[lumise.current_stage].type != undefined 
	// 			&& stage_arr[lumise.current_stage].type == 'printful' 
	// 			&& stage_arr[lumise.current_stage].additional_price != undefined 
	// 			&& stage_arr[lumise.current_stage].additional_price != null 
	// 			&& stage_arr[lumise.current_stage].additional_price != ''
	// 		)
	// 		&&
	// 		(
	// 			// object in stage
	// 			(
	// 				obj.evented == true
	// 			)
	// 			||
	// 			// template in stage
	// 			(
	// 				obj.template !== undefined && 
	// 				typeof obj.template == 'object' &&
	// 				lumise.cart.template[s].indexOf(obj.template[0]) === -1
	// 			)
	// 		)
	// 	){

	// 		let add_price = true;
	// 		let count_obj = 0;
	// 		lumise.stage().canvas.getObjects().map(function(obj){
	// 			if(obj.evented == true){
	// 				add_price = false;
	// 				count_obj++;
	// 			}
	// 		});
	// 		if(add_price == true){
	// 			lumise.cart.price.base = lumise.cart.price.base+getPriceStage;
	// 		}
	// 		if(count_obj >= 1){
	// 			lumise.cart.price.base = lumise.cart.price.base-getPriceStage;
	// 		}

	// 		lumise.cart.price.base += parseFloat(stage_arr[lumise.current_stage].additional_price);
	// 		delete stage_arr[lumise.current_stage];
	// 	}
	// });

	lumise.add_action('load_object_stages', function(){
		let stageName = lumise.stage().name;
		var getPriceStage = parseFloat($('div#lumise-stage-nav li[data-stage="'+stageName+'"]').attr('data-additional_price'));
		if(isNaN(getPriceStage) || getPriceStage == undefined || getPriceStage == 'undefined' || getPriceStage.toString().trim() == ''){
			getPriceStage = 0;
		}

		let count_obj = 0;
		lumise.stage().canvas.getObjects().map(function(obj){
			if(obj.evented == true){
				add_price = false;
				count_obj++;
			}
		});
		if(count_obj >= 1){
			lumise.cart.price.base = lumise.cart.price.base+getPriceStage;
		}
	});


	lumise.add_action('price_action', function(action){

		// let product_base = lumise.fn.url_var('product_base', '');
		// let product_cms = lumise.fn.url_var('product_cms', '');
		let stageName = lumise.stage().name;

		// let session_name = product_base+'_'+product_cms+'_'+stageName+'_canvasBase';
		// let countObj = lumise.stage().canvas.getObjects().length-1;

		var getPriceStage = parseFloat($('div#lumise-stage-nav li[data-stage="'+stageName+'"]').attr('data-additional_price'));
		if(isNaN(getPriceStage) || getPriceStage == undefined || getPriceStage == 'undefined' || getPriceStage.toString().trim() == ''){
			getPriceStage = 0;
		}

		// if(action == 'add'){
		// 	if(sessionStorage.getItem(session_name) === null){
		// 		sessionStorage.setItem(session_name, lumise.stage().canvas.getObjects().length-1);
		// 	}
		// 	if(getPriceStage != 0 && getPriceStage > 0 && sessionStorage.getItem(session_name) == countObj ){
		// 		lumise.cart.price.base = lumise.cart.price.base+getPriceStage;
		// 	}
		// }

		// if(action == 'del' && sessionStorage.getItem(session_name) !== null && sessionStorage.getItem(session_name) == countObj-1){
		// 	sessionStorage.removeItem(session_name);
		// 	if(getPriceStage != 0 && getPriceStage > 0){
		// 		lumise.cart.price.base = lumise.cart.price.base-getPriceStage;
		// 	}
		// }

		if(action == 'add'){
			let add_price = true;
			lumise.stage().canvas.getObjects().map(function(obj){
				if(obj.evented == true){
					add_price = false;
				}
			});
			if(add_price == true && getPriceStage > 0){
				lumise.cart.price.base = lumise.cart.price.base+getPriceStage;
			}
		}

		if(action == 'del'){
			let count_obj = 0;
			lumise.stage().canvas.getObjects().map(function(obj){
				if(obj.evented == true){
					count_obj++;
				}
			});
			if(count_obj == 1 && getPriceStage > 0){
				lumise.cart.price.base = lumise.cart.price.base-getPriceStage;
			}
		}

		if(action == 'remove'){
			let count_obj = 0;
			lumise.stage().canvas.getObjects().map(function(obj){
				if(obj.evented == true){
					count_obj++;
				}
			});
			if(count_obj == 0 && getPriceStage > 0){
				lumise.cart.price.base = lumise.cart.price.base-getPriceStage;
			}
		}

	});


	lumise.add_filter('filter_current_design', function(curent_designs, new_stages) {
		if(
			new_stages != undefined 
			&& Object.keys(new_stages)[0] != undefined 
			&& new_stages[Object.keys(new_stages)[0]].addon != undefined 
			&& new_stages[Object.keys(new_stages)[0]].addon.type != undefined 
			&& new_stages[Object.keys(new_stages)[0]].addon.type == 'printful'
		){
			Object.keys(curent_designs).map(function(key, index){
				if(curent_designs[key].data != undefined){
					delete curent_designs[key].data;
				}
			});
		}

		return curent_designs;
		
	});

}