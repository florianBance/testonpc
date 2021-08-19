var focus="";

function string_to_slug (str) {
    str = str.replace(/^\s+|\s+$/g, ''); // trim
    str = str.toLowerCase();

    // remove accents, swap ñ for n, etc
    var from = "àáäâèéëêìíïîòóöôùúüûñç·/_,:;";
    var to   = "aaaaeeeeiiiioooouuuunc------";
    for (var i=0, l=from.length ; i<l ; i++) {
        str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
    }

    str = str.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
        .replace(/\s+/g, '-') // collapse whitespace and replace by -
        .replace(/-+/g, '-'); // collapse dashes

    return str;
}

$( document ).ready(function() {

    var verifEml=/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]{2,}[.][a-zA-Z]{2,5}$/

    $('#article_form_title').keyup(function (){
        $valTitle=$(this).val();
        $slug=string_to_slug($valTitle);
        $('#article_form_slug').val($slug);
    })


    $('form[name="article_form"]').submit(function() {
        focus="";
        $('.invalid-feedback').remove();
        $('*').removeClass('is-invalid');
        if($('#article_form_title').val()==""){
            $('#article_form_title').addClass('is-invalid');
            $('#article_form_title').prev('label').append(msg);
            if(focus=="") focus='#article_form_title';
        }
        if($('#article_form_text').val()==""){
            $('#article_form_text').addClass('is-invalid');
            $('#article_form_text').prev('label').append(msg);
            if(focus=="") focus='#article_form_text';
        }
        if($('#article_form_slug').val()==""){
            $('#article_form_slug').addClass('is-invalid');
            $('#article_form_slug').prev('label').append(msg);
            if(focus=="") focus='#article_form_slug';
        }


        if(focus!=''){
            $(focus).focus();
            return false;
        }
        return true;
    });
});