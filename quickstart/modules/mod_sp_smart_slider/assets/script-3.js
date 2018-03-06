
jQuery(window).load(function(){

    document.adminForm.onsubmit = function(){
        jQuery('#sp-slider-element').remove();
    }

    var str_replace = function(char_map, content) {
        jQuery.each(char_map, function(character, regex) {
            content = content.replace(character, regex);
        });
        return content;
    };

    var replacesArray = {
        0 : 5,
        1 : 6,
        2 : 7,
        3 : 8
    }


    jQuery("#sliders-slide-addnew select, .sp-element select").removeClass("chzn-done").css('display', 'inline').data('chosen', null);
    jQuery("#sliders-slide-addnew, .sp-element").find("*[class*=chzn]").remove();

    jQuery('#moduleOptions > div:eq(0)').addClass('sp-basic-tab');
    jQuery('#moduleOptions > div:eq(1)').addClass('sp-sliders-tab');
    jQuery('#moduleOptions > div:eq(2)').addClass('sp-layout-settings-tab');
    jQuery('#moduleOptions > div:eq(3)').addClass('sp-advanced-tab');

    ;(function() {
        SqueezeBox.assign('.model', { parse: 'rel' });
    }).delay(1000);

    $addon = jQuery('#jform_params_sp_style option:selected').text();

    jQuery('#moduleOptions > div:eq(2) > .accordion-heading a').text($addon+' Settings');

//Change animation name Joomla 3.2
jQuery("#myTabTabs>li").each(function(){
    var target = jQuery(this).find('>a').prop('hash');
    if( target == '#attrib-animation'){
        jQuery(this).find('>a').text($addon+' Settings');
    }
});


var href    = jQuery('#sp-animation-setting-panel .accordion-heading a');
var genhref = str_replace(replacesArray, href.attr('href')); 
jQuery('#sp-animation-setting-panel .accordion-heading a').attr('href', genhref);

var id    = jQuery('#sp-animation-setting-panel .accordion-group > .accordion-body');
var genid = str_replace(replacesArray, id.attr('id')); 
jQuery('#sp-animation-setting-panel .accordion-group > .accordion-body').attr('id', genid);

jQuery('#sliders-slide-addnew').parent().addClass('sp-slider-option-container');
jQuery('body').delegate("#jform_params_sp_style",'change', function(){

    $addon = jQuery(this).find('option:selected').text();

    jQuery('#moduleOptions > div:eq(2) > .accordion-heading a').text($addon+' settings');

//Change animation name Joomla 3.2
jQuery("#myTabTabs>li").each(function(){
    var target = jQuery(this).find('>a').prop('hash');
    if( target == '#attrib-animation'){
        jQuery(this).find('>a').text($addon+' Settings');
    }
});

$ver = eval('anim'+jQuery(this).val());

//  push html and add chosen
jQuery('#sp-animation-setting-panel').html(jQuery($ver).html());

var href    = jQuery('#sp-animation-setting-panel .accordion-heading a');
var genhref = str_replace(replacesArray, href.attr('href')); 
jQuery('#sp-animation-setting-panel .accordion-heading a').attr('href', genhref);

var id    = jQuery('#sp-animation-setting-panel .accordion-group > .accordion-body');
var genid = str_replace(replacesArray, id.attr('id')); 
jQuery('#sp-animation-setting-panel .accordion-group > .accordion-body').attr('id', genid);

/*****/

jQuery('.radio.btn-group label').addClass('btn');
jQuery(".btn-group label:not(.active)").click(function()
{
    var label = jQuery(this);
    var input = jQuery('#' + label.attr('for'));

    if (!input.prop('checked')) {
        label.closest('.btn-group').find("label").removeClass('active btn-success btn-danger btn-primary');
        if (input.val() == '') {
            label.addClass('active btn-primary');
        } else if (input.val() == 0) {
            label.addClass('active btn-danger');
        } else {
            label.addClass('active btn-success');
        }
        input.prop('checked', true);
    }
});
jQuery(".btn-group input[checked=checked]").each(function()
{
    if (jQuery(this).val() == '') {
        jQuery("label[for=" + jQuery(this).attr('id') + "]").addClass('active btn-primary');
    } else if (jQuery(this).val() == 0) {
        jQuery("label[for=" + jQuery(this).attr('id') + "]").addClass('active btn-danger');
    } else {
        jQuery("label[for=" + jQuery(this).attr('id') + "]").addClass('active btn-success');
    }
});

});   // end change event delegate

jQuery('#sp-add-slide').on('click', function(){
    jQuery('.sp-toggle-element-main').slideToggle('fast');
});

jQuery('#sp-slide-add').on('click', function () {

    $html = '';
    $html = jQuery('#sp-slider-element').html();
    $htmlclone = jQuery('#sp-slider-element').clone();

    var formObjects = jQuery('#sp-slider-element').find('input, select, textarea').serializeArray();

    $title = jQuery('#sp-slider-element').find('[ref="title"]').val();
    $state = jQuery('#sp-slider-element').find('select.sp-state').val();

    $sourcename = jQuery('#sp-slider-element').find('select.sp-sliders-source-type option:selected').text();

    if ($title.trim() == '') {
        return;
    }

    sp_item_increment += 1;


    $htmlclone.find('li.sp-sliders-source-type-li').hide();

    $htmlclone.find('input, select, a').each(function () {


        if (typeof jQuery(this).attr('id') != 'undefined') {
            jQuery(this).attr('id', jQuery(this).attr('id').replace(/\b(item|image)-([0-9]+)\b/g, '$1-added-' + sp_item_increment));

            if (jQuery(this).is("select")) {
                jQuery(this).removeAttr('id');
            }


        }


        if (typeof jQuery(this).attr('url') != 'undefined') {
            jQuery(this).attr('url', jQuery(this).attr('url').replace(/\b(item|image)-([0-9]+)\b/g, '$1-added-' + sp_item_increment));
        }


        if (typeof jQuery(this).attr('ref') != 'undefined') {

            jQuery(this).attr('ref', jQuery(this).attr('ref').replace(/\b(item|image)-([0-9]+)\b/g, '$1-added-' + sp_item_increment));
        }


    });


    $ghtml = '<li class="sp-element-wrapper"><div class="sp-flt-left"><span class="sp-move"></span></div><span class="sp-title sp-toggler">' + $title + '&nbsp;&nbsp;::&nbsp;&nbsp;<span class="sp-title-source">' + $sourcename + '</span></span><div class="sp-flt-right"><span class="sp-edit"></span>    <span ref="state" class="sp-' + $state + '"></span>   <span class="sp-delete"></span> </div><div style="clear:both"></div> <div class="sp-toggle-element" >';
    $ghtml += '<ul class="sp-element">';
    $ghtml += '</ul></div></li>';


    $htmlclone.find('input, select, textarea').each(function (i, ele) {
        var value = formObjects[i].value;
        jQuery(ele).val(jQuery.trim(value));
    });

    jQuery('#sliders-slide-list').append($ghtml);
    jQuery('#sliders-slide-list > li:last ul.sp-element').append($htmlclone.children());
    jQuery('#sliders-slide-list > li:last div.sp-toggle-element').slideToggle();


    jQuery('.sp-toggle-element-main').slideUp('fast');


    (function () {
        SqueezeBox.assign('.model', { parse: 'rel' });
    }).delay(1000);

    jQuery('#sliders-slide-list').sortable();

});


jQuery(document).on('click', 'span.sp-edit', function () {

    jQuery(this).closest('li').find('>.sp-toggle-element').slideToggle();

});

jQuery(document).on('change', 'select.sp-sliders-source-type', function(){

    eval( "var $ver=(" + jQuery(this).val()+'HTML' + ")" );
    jQuery(this).parent().nextAll().remove();
    $ver.replace(/\b(item|image)-([0-9]+)\b/g,'$1-saved-'+(sp_item_increment+1));
    jQuery(this).parent().after($ver);

    ;(function() {
        SqueezeBox.assign('a.model', { parse: 'rel' });
    }).delay(500);
});

jQuery(document).on('click', '.sp-delete', function() {

    conf = confirm('Are you sure to delete?');
    if( conf ){
        jQuery(this).closest('li').remove();
    }

});

jQuery(document).on('change', '.sp-toggle-element select.sp-state', function(){
    var e = this.parent().parent().parent().parent();
    e.find('span[ref="state"]').set('class','sp-'+ jQuery(this).val()); 
});

jQuery(document).on('click', 'span[ref="state"]', function()
{
    var e = jQuery(this).parent()
    .parent()
    .find('.sp-toggle-element select.sp-state');

    if( e.val() == 'published' )
    {
        e.val('unpublished');
        jQuery(this).addClass('sp-unpublished').removeClass('sp-published');
    } else {
        e.val('published');
        jQuery(this).addClass('sp-published').removeClass('sp-unpublished');
    }
});


jQuery(document).on('blur', '.sp-toggle-element input[ref="title"]', function(){
    jQuery(this).parent().parent().parent().parent().find('span.sp-title').html(this.val().trim());
});


jQuery('#sliders-slide-list').sortable();
jQuery('.sp-toggle-element, .sp-toggle-element-main').hide();

jQuery(document).on('click', '#sliders-slide-list li .sp-title.sp-toggler', function(){
    jQuery(this).parent().find('.sp-toggle-element').slideToggle('fast');
});

});