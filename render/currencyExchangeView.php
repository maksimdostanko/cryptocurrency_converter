<?php
$currencyServerData = new CurrencyServerData();
$currencyList = $currencyServerData->getCurrenciesList();
//$currencyServerData->debugInfo();
?>

	<div class="exchange-box">
		<div class="exchange-left">
			<div class="exchange-from">
				<input type="number" id="number_from" name="number_from" placeholder="Enter the amount">
				<?php
				renderCombo($currencyList, "currency_from", "");
				?>
			</div>
		</div>

		<div class="exchange-center">
			<i class="fas fa-angle-left"></i>
			<i class="fas fa-angle-right"></i>
		</div>

		<div class="exchange-right">
			<div class="exchange-from">
				<input type="number" id="number_to" name="number_to" placeholder="Enter the amount">
				<?php
				renderCombo($currencyList, "currency_to");
				?>
			</div>
		</div>
	</div>

	<script>
		jQuery(function () {
			jQuery(".currency_from").change(function () {
				exchange(false, true)
			});
			jQuery(".currency_to").change(function () {
				exchange(false, true)
			});
			jQuery("#number_from").bind("propertychange keyup input cut paste", function (event) {
				exchange(false, false);
			});
			jQuery("#number_to").bind("propertychange keyup input cut paste", function (event) {
				exchange(true, false);
			});

			jQuery(".exchange-center").click(function () {// swap
				let currency_symbol_from = getSelectCurrencySymbol(".currency_from");
				let currency_symbol_to = getSelectCurrencySymbol(".currency_to");
				setSelectCurrencySymbol(".currency_from",currency_symbol_to);
				setSelectCurrencySymbol(".currency_to",currency_symbol_from);
				$('.selectpicker').selectpicker('refresh');
				exchange(false, true)
			});
			jQuery("#number_from").val(1);
      		setSelectCurrencySymbol(".currency_to","ETH");

		});

		function exchange(reverse, reload) {
			gReverce = reverse;
			if (reload) {
				gCurrancyRate = 0;
				jQuery("#number_to").val("");
				fromServer(true);
			} else {
				calculate_exchange();
			}
		}

		function calculate_exchange() {
			if (gReverce) {
				let countToExchange = jQuery("#number_to").val();
				jQuery("#number_from").val(countToExchange * gCurrencyRate);
			} else {
				let countToExchange = jQuery("#number_from").val();
				let count = countToExchange / gCurrencyRate;
				jQuery("#number_to").val(count);
			}
		}


		gCurrencyRate = 0;
		gReverce = 0;
		jQuery(function () {
			fromServer(false);
		});

		function getSelectCurrencySymbol(el) {
          return jQuery(el).find(':selected').val();
		}

		function setSelectCurrencySymbol(el,val) {
			jQuery(el).val(val);
		}

		function fromServer(log_it) {
			let currency_symbol_from = getSelectCurrencySymbol(".currency_from");
			let currency_symbol_to = getSelectCurrencySymbol(".currency_to");
			let data = {
				action: 'getRate',
				currency_symbol_from: currency_symbol_from,
				currency_symbol_to: currency_symbol_to,
				log_it: log_it,
				_ajax_nonce: "<?php echo wp_create_nonce('getJSONCurrencyData'); ?>"
			};
			let ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

			jQuery.post(ajaxurl, data, function (response) {
				if (response) {
					try {
						gCurrencyRate = JSON.parse(response);
						calculate_exchange(gReverce);
					} catch (e) {
						console.log(e);
					}
				}
			});

		}


		function dynamicOptions() {
			//gCurrencyRate = JSON.parse(response);
			// // dynamically
			// let responseObj = JSON.parse(response);
			// let options="";
			// responseObj.forEach(row => {
			// 	//console.log(obj);
			// 	options+=`<option data-tokens="${row.symbol}" data-rate="${row.price}"> ${row.name} / ${row.symbol}</option>`
			// 	}
			// );
			// jQuery("#currency_from").html(options);
			// jQuery("#currency_to").html(options);
		}


	</script>


<?php


function renderCombo($currencyList, $className = "", $add = "")
{
	$imagesFolder = CURRENCY_EXCHANGE_PLUGIN_DIR . 'images/';
	$imagesUrl = CURRENCY_EXCHANGE_PLUGIN_URL. 'images/';
	?>
	<select id="<?= $className ?>" class="selectpicker <?= $className ?>" data-live-search="true" <?= $add ?>>
		<?php
		foreach ($currencyList as $currencyObj) {
			$imgFileName = $currencyObj->symbol . ".svg";
			$img = file_exists($imagesFolder . $imgFileName) ? $imagesUrl . $imgFileName : $imagesUrl . "NONE.svg";
			$imgHTML = "<img src='$img' width='36px'>";
			?>
			<option value="<?= $currencyObj->symbol ?>"
				data-content="<?= $imgHTML ?> <?= $currencyObj->name ?> / <?= $currencyObj->symbol ?>"
				data-tokens="<?= $currencyObj->symbol ?>"
				data-currency_symbol="<?= $currencyObj->symbol ?>">
				<?= $currencyObj->name ?> / <?= $currencyObj->symbol ?></option>
			<?php
		}
		?>
	</select>
	<?php
}
