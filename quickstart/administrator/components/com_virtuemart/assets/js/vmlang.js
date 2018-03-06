var oldflag = "";
function updateLanguageValues(data, langCode, flagClass) {
	var items = [];

	var theForm = document.forms["adminForm"];
	if(typeof theForm.vmlang==="undefined"){
		var input = document.createElement("input");
		input.type = "hidden";
		input.name = "vmlang";
		input.value = langCode;
		theForm.appendChild(input);
	} else {
		theForm.vmlang.value = langCode;
	}
	if (data.fields !== "error" ) {
		var cible = null; 
		var tmce_ver = 0;
		if(typeof window.tinyMCE!=="undefined"){
			var tmce_ver=window.tinyMCE.majorVersion;
		}
		
		if (data.structure == "empty") alert(data.msg);

		jQuery.each(data.fields , function(key, val) {
			cible = jQuery("#"+key);
			if (window.oldflag !== "") jQuery('.allflags').removeClass(window.oldflag)

			if (! cible.parent().hasClass(flagClass)) {
				if (tmce_ver >= "4") {
					if ((cible.parent().addClass('allflags ' + flagClass).children().hasClass("mce_editable") || cible.parent().children().hasClass("wf-editor")) && data.structure !== "empty") {
						tinyMCE.get(key).execCommand("mceSetContent", false, val);
						cible.val(val);
					} else if (data.structure !== "empty") cible.val(val);
				} else {
					if (cible.parent().addClass('allflags ' + flagClass).children().hasClass("mce_editable") && data.structure !== "empty") {
						tinyMCE.execInstanceCommand(key, "mceSetContent", false, val);
						cible.val(val);
					} else if (data.structure !== "empty") cible.val(val);
				}
			}
			//custom for child products: 
			if (typeof data.requested_id != 'undefined')
			if (key == 'product_name') {
				var cible = jQuery("#child"+data.requested_id+"product_name");
				
				cible.parent().addClass('allflags '+flagClass);
				cible.val(data.fields.product_name);
				jQuery("#child"+data.requested_id+"slug").val(data.fields["slug"]);
			}
		});

		var fbflag = '';
		if (data.byfallback != "0"){
			fbflag = '(<span class="allflags flag-'+data.byfallback+'"></span>)';
		}
		jQuery(".langfallback").html(fbflag);

	} else {
		alert(data.msg);
	}
	
	
}


function updateLanguageVars(el, event) {
	
	var langCode = jQuery(el).val();
	var flagClass = "flag-"+langCode;
	var config = jQuery(el).data('json'); 
	config.lg = langCode; 
	//console.log(config); 
	jQuery.ajax({
type: "POST",
cache: false,
dataType: "json",
data: config,
url: "index.php?option=com_virtuemart&view=translate&task=paste&format=json&token=",
	}).done(
	function(data) {
		//console.log(data); 
		if (typeof data.multiple !== 'undefined') {
			for (var i=0; i<data.multiple.length; i++) {
				updateLanguageValues(data.multiple[i], langCode, flagClass); 
			}
		}
		else {
			updateLanguageValues(data, langCode, flagClass); 
		}
		window.oldflag = flagClass ;	
		
		
	});

}

