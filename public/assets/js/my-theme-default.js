// SCROLL DO NAVBAR
var lastScrollTop = 0;
var delta = 80;
var navbarHeight = $('#navbar').outerHeight();
var navbarHeight2 = $('#navbar-mobile').outerHeight();
$(window).scroll(function(event){
    hasScrolled();
});
function hasScrolled() {
    var st = $(this).scrollTop();
    // Make sure they scroll more than delta
    if(Math.abs(lastScrollTop - st) <= delta)
        return;

    // If they scrolled down and are past the navbar, add class .nav-up.
    // This is necessary so you never see what is "behind" the navbar.
    if (st > lastScrollTop && st > navbarHeight){
        // Scroll Down
        $('#navbar').removeClass('nav-down').addClass('nav-up');
    } else {
        $('#navbar').removeClass('nav-up').addClass('nav-down');

        if(st + $(window).height() < $(document).height()) {
        }
    }

    if (st > lastScrollTop && st > navbarHeight2){
        // Scroll Down
        $('#navbar-mobile').removeClass('nav-down').addClass('nav-up');
    } else {
        $('#navbar-mobile').removeClass('nav-up').addClass('nav-down');

        if(st + $(window).height() < $(document).height()) {
        }
    }

    lastScrollTop = st;
}

//MUDAR NOME DO ARQUIVO
$("#upload").change(function () {
    document.getElementById("fotoText").innerHTML = "<span class=\"mdi mdi-18px mdi-file-outline\"></span> "+$(this).val().replace(/^.*\\/, "")+"<span class=\"mdi mdi-check\"></span>"+"";
});

/*FUNÇÃO VOLTAR NO NAVEGADO*/
function goBack() {
    window.history.back();
}

//FUNÇÃO MASCARAS
function mascara(o,f){
    v_obj=o
    v_fun=f
    setTimeout("execmascara()",1)
}

function execmascara(){
    v_obj.value=v_fun(v_obj.value)
}

function mplaca(v){
    v=v.replace(/^[A-Z]{3}\\d[A-Z]\\d{2}$/,"$1-$2");         //Esse é tão fácil que não merece explicações
    return v
}

function mcep(v){
    v=v.replace(/\D/g,"");                    //Remove tudo o que não é dígito
    v=v.replace(/^(\d{5})(\d)/,"$1-$2");         //Esse é tão fácil que não merece explicações
    return v
}

function mtel(v){
    v=v.replace(/\D/g,"");             //Remove tudo o que não é dígito
    v=v.replace(/^(\d{2})(\d)/g,"($1) $2"); //Coloca parênteses em volta dos dois primeiros dígitos
    v=v.replace(/(\d)(\d{4})$/,"$1-$2");    //Coloca hífen entre o quarto e o quinto dígitos
    return v;
}

function mcel(v){
    v=v.replace(/\D/g,"");             //Remove tudo o que não é dígito
    v=v.replace(/^(\d{2})(\d)/g,"($1) $2"); //Coloca parênteses em volta dos dois primeiros dígitos
    v=v.replace(/(\d)(\d{4})$/,"$1-$2");    //Coloca hífen entre o quarto e o quinto dígitos
    return v;
}

function mcnpj(v){
    v=v.replace(/\D/g,"")                           //Remove tudo o que não é dígito
    v=v.replace(/^(\d{2})(\d)/,"$1.$2")             //Coloca ponto entre o segundo e o terceiro dígitos
    v=v.replace(/^(\d{2})\.(\d{3})(\d)/,"$1.$2.$3") //Coloca ponto entre o quinto e o sexto dígitos
    v=v.replace(/\.(\d{3})(\d)/,".$1/$2")           //Coloca uma barra entre o oitavo e o nono dígitos
    v=v.replace(/(\d{4})(\d)/,"$1-$2")              //Coloca um hífen depois do bloco de quatro dígitos
    return v
}

function mcpf(v){
    v=v.replace(/\D/g,"")                    //Remove tudo o que não é dígito
    v=v.replace(/(\d{3})(\d)/,"$1.$2")       //Coloca um ponto entre o terceiro e o quarto dígitos
    v=v.replace(/(\d{3})(\d)/,"$1.$2")       //Coloca um ponto entre o terceiro e o quarto dígitos
                                             //de novo (para o segundo bloco de números)
    v=v.replace(/(\d{3})(\d{1,2})$/,"$1-$2") //Coloca um hífen entre o terceiro e o quarto dígitos
    return v
}

function mdata(v){
    v=v.replace(/\D/g,"");                    //Remove tudo o que não é dígito
    v=v.replace(/(\d{2})(\d)/,"$1/$2");
    v=v.replace(/(\d{2})(\d)/,"$1/$2");

    v=v.replace(/(\d{2})(\d{2})$/,"$1$2");
    return v;
}

function mtempo(v){
    v=v.replace(/\D/g,"");                    //Remove tudo o que não é dígito
    v=v.replace(/(\d{1})(\d{2})(\d{2})/,"$1:$2.$3");
    return v;
}

function mhora(v){
    v=v.replace(/\D/g,"");                    //Remove tudo o que não é dígito
    v=v.replace(/(\d{2})(\d)/,"$1:$2");
    return v;
}

function mrg(v){
    v=v.replace(/\D/g,"");                                      //Remove tudo o que não é dígito
    v=v.replace(/(\d)(\d{7})$/,"$1.$2");    //Coloca o . antes dos últimos 3 dígitos, e antes do verificador
    v=v.replace(/(\d)(\d{4})$/,"$1.$2");    //Coloca o . antes dos últimos 3 dígitos, e antes do verificador
    v=v.replace(/(\d)(\d)$/,"$1-$2");               //Coloca o - antes do último dígito
    return v;
}

function mnum(v){
    v=v.replace(/\D/g,"");                                      //Remove tudo o que não é dígito
    return v;
}

function mvalor(v){
    v=v.replace(/\D/g,"");//Remove tudo o que não é dígito
    v=v.replace(/(\d)(\d{8})$/,"$1.$2");//coloca o ponto dos milhões
    v=v.replace(/(\d)(\d{5})$/,"$1.$2");//coloca o ponto dos milhares

    v=v.replace(/(\d)(\d{2})$/,"$1,$2");//coloca a virgula antes dos 2 últimos dígitos
    return v;
}

function mvalor2(i) {
    var v = i.toString().replace(/\D/g,'');
    v = (v/100).toFixed(2) + '';
    v = v.replace(".", ",");
    v = v.replace(/(\d)(\d{3})(\d{3}),/g, "$1.$2.$3,");
    v = v.replace(/(\d)(\d{3}),/g, "$1.$2,");
    i = v;
    return i;
}

function ncartao(v){
    v = v.replace(/\D/g,""); // Permite apenas dígitos
    v = v.replace(/(\d{4})/g, "$1."); // Coloca um ponto a cada 4 caracteres
    v = v.replace(/\.$/, ""); // Remove o ponto se estiver sobrando
    //v = v.substring(0, 19)// Limita o tamanho
    return v;
}

function cpfCnpj(v){

    //Remove tudo o que não é dígito
    v=v.replace(/\D/g,"")

    if (v.length < 14) { //CPF

        v=v.replace(/(\d{3})(\d)/,"$1.$2")       //Coloca um ponto entre o terceiro e o quarto dígitos
        v=v.replace(/(\d{3})(\d)/,"$1.$2")       //Coloca um ponto entre o terceiro e o quarto dígitos//de novo (para o segundo bloco de números)
        v=v.replace(/(\d{3})(\d{1,2})$/,"$1-$2") //Coloca um hífen entre o terceiro e o quarto dígitos

    } else { //CNPJ

        v = v.replace( /^(\d{2})(\d)/ , "$1.$2"); //Coloca ponto entre o segundo e o terceiro dígitos
        v = v.replace( /^(\d{2})\.(\d{3})(\d)/ , "$1.$2.$3"); //Coloca ponto entre o quinto e o sexto dígitos
        v = v.replace( /\.(\d{3})(\d)/ , ".$1/$2"); //Coloca uma barra entre o oitavo e o nono dígitos
        v = v.replace( /(\d{4})(\d)/ , "$1-$2"); //Coloca um hífen depois do bloco de quatro dígitos

    }

    return v
}

function site(v){
//Esse sem comentarios para que você entenda sozinho;-)
    v=v.replace(/^http:\/\/?/,"")
    dominio=v
    caminho=""
    if(v.indexOf("/")>-1)
        dominio=v.split("/")[0]
    caminho=v.replace(/[^\/]*/,"")
    dominio=dominio.replace(/[^\w\.\+-:@]/g,"")
    caminho=caminho.replace(/[^\w\d\+-@:\?&=%\(\)\.]/g,"")
    caminho=caminho.replace(/([\?&])=/,"$1")
    if(caminho!="")dominio=dominio.replace(/\.+$/,"")
    v="http://"+dominio+caminho
    return v
}

function soLetras(v){
    return v.replace(/\d/g,"") //Remove tudo o que não é Letra
}

function soLetrasMA(v){
    v=v.toUpperCase() //Maiúsculas
    return v.replace(/\d/g,"") //Remove tudo o que não é Letra ->maiusculas
}

function soLetrasMI(v){
    v=v.toLowerCase() //Minusculas
    return v.replace(/\d/g,"") //Remove tudo o que não é Letra ->minusculas
}


//FIM DE MASCARAS

function validaData(data) {
    var expReg = /^(([0-2]\d|[3][0-1])\/([0]\d|[1][0-2])\/[1-2][0-9]\d{2})$/;

    var msgErro = 'Formato inválido de data.';

    if ((data.match(expReg)) && (data!='')) {
        var dia = data.substring(0,2);
        var mes = data.substring(3,5);
        var ano = data.substring(6,10);

        if (((mes == 4) || (mes == 6) || (mes == 9) || (mes == 11)) && (dia > 30)) {
            //alert("Dia incorreto!!! O mês especificado contém no máximo 30 dias.");
            return false;
        } else {
            if ( (ano%4!=0) && (mes == 2) && (dia > 28)) {
                //alert("Data incorreta!!! O mês especificado contém no máximo 28 dias.");
                return false;
            } else {
                if ((ano%4 == 0) && (mes == 2) && (dia > 29)) {
                    //alert("Data incorreta!!! O mês especificado contém no máximo 29 dias.");
                    return false;
                } else {
                    //alert ("Data correta!");
                    return true;
                }
            }
        }
    } else {
        //alert(msgErro);
        //campo.focus();
        return false;
    }
}

//REMOVE OQUE NÃO FOR DIGITO
function removeCaracteres(str) {
    return str.replace(/[^\d]+/g,'')
}

function formatTextAspasUsuario(texto){
    renderedString = texto.replace(/\\./g, function (match) {
        return (new Function('return "' + match + '"'))() || match;
    });
    return renderedString;
}

function formatFloat(str) {
    str = str.replace('.','');
    return str.replace(',','.');
}

// MÁSCARA DE VALORES
// onkeyup="return txtBoxFormat(this, '99/99/9999', event);"
function txtBoxFormat(objeto, sMask, evtKeyPress) {
    var i, nCount, sValue, fldLen, mskLen,bolMask, sCod, nTecla;

    if(document.all) {
        // Internet Explorer
        nTecla = evtKeyPress.keyCode;
    } else if(document.layers) {
        // Nestcape
        nTecla = evtKeyPress.which;
    } else {
        nTecla = evtKeyPress.which;

        if ((nTecla == 8) || (nTecla == 13) || (nTecla == 0)) {
            return true;
        }
    }

    sValue = objeto.value;

    // Limpa todos os caracteres de formatação que
    // já estiverem no campo.
    sValue = sValue.toString().replace( "-", "" );
    sValue = sValue.toString().replace( "-", "" );
    sValue = sValue.toString().replace( ".", "" );
    sValue = sValue.toString().replace( ".", "" );
    sValue = sValue.toString().replace( "/", "" );
    sValue = sValue.toString().replace( "/", "" );
    sValue = sValue.toString().replace( ":", "" );
    sValue = sValue.toString().replace( ":", "" );
    sValue = sValue.toString().replace( "(", "" );
    sValue = sValue.toString().replace( "(", "" );
    sValue = sValue.toString().replace( ")", "" );
    sValue = sValue.toString().replace( ")", "" );
    sValue = sValue.toString().replace( " ", "" );
    sValue = sValue.toString().replace( " ", "" );
    fldLen = sValue.length;
    mskLen = sMask.length;

    i = 0;
    nCount = 0;
    sCod = "";

    if (objeto.value.length == 0) {
        mskLen = fldLen;
    } else {
        mskLen = fldLen - 1;
    }

    while (i <= mskLen) {
        bolMask = ((sMask.charAt(i) == "-") || (sMask.charAt(i) == ".") || (sMask.charAt(i) == "/") || (sMask.charAt(i) == ":"))
        bolMask = bolMask || ((sMask.charAt(i) == "(") || (sMask.charAt(i) == ")") || (sMask.charAt(i) == " "))

        if (bolMask) {
            sCod += sMask.charAt(i);
            mskLen++;
        } else {
            sCod += sValue.charAt(nCount);
            nCount++;
        }

        i++;
    }

    objeto.value = sCod;

    if (nTecla != 8) {
        // backspace
        if (sMask.charAt(i-1) == "9") {
            // apenas números...
            return ((nTecla > 47) && (nTecla < 58));
        } else {
            // qualquer caracter...
            return false;
        }
    } else {
        return true;
    }
}

//REMOVE OQUE NÃO FOR DIGITO
function formatUrl(texto) {
    texto = minuscula(texto);
    texto = removerAcentos(texto);

    texto = texto.replace(/[^\w\-]+/g, '-');

    return texto;
}

function formatDatePtBr(dataISO) {
    const [ano, mes, dia] = dataISO.split('-');
    return `${dia}/${mes}/${ano}`;
}

function formatGoogleMaps(texto) {
    texto = texto.replace(/[^\w\-]+/g, '%20');
    return texto;
}

function removerAcentos(s){
    var map={"â":"a","Â":"A","à":"a","À":"A","á":"a","Á":"A","ã":"a","Ã":"A","ê":"e","Ê":"E","è":"e","È":"E","é":"e","É":"E","î":"i","Î":"I","ì":"i","Ì":"I","í":"i","Í":"I","õ":"o","Õ":"O","ô":"o","Ô":"O","ò":"o","Ò":"O","ó":"o","Ó":"O","ü":"u","Ü":"U","û":"u","Û":"U","ú":"u","Ú":"U","ù":"u","Ù":"U","ç":"c","Ç":"C"};
    return s.replace(/[\W\[\] ]/g,function(a){return map[a]||a})
}


function excluirItemTabela(id) {
    Swal.fire({
        title: 'Deseja excluir esse item?',
        text: 'Uma vez deletado, você não poderá recuperar este item!',
        type: 'question',
        showCancelButton: true,
        confirmButtonColor: '#880000',
        confirmButtonText: 'Sim',
        cancelButtonText: 'Não',
    })
        .then((result) => {
            if (result.value) {
                document.getElementById('ID_DELETE').value = id;
                document.getElementById('formExcluir').submit();
            }
        });
}

function ativarProgress(campo, progress) {
    if (campo != null) {
        campo.fadeOut(0);
    }
    progress.fadeIn(0);
}

function cancelarProgress(campo, progress) {
    if (campo != null) {
        campo.fadeIn(0);
    }
    progress.fadeOut(0);
}

function ativarProgressJs(campo, progress) {
    campo.style.display = "none";
    progress.style.display = "";
}

function cancelarProgressJs(campo, progress) {
    campo.style.display = "";
    progress.style.display = "none";
}

function primeiraLetraMaiusculaCadaPalavra(text) {
    var loweredText = text.toLowerCase();
    var words = loweredText.split(" ");
    for (var a = 0; a < words.length; a++) {
        var w = words[a];

        var firstLetter = w[0];

        if (w.length > 2) {
            w = firstLetter.toUpperCase() + w.slice(1);
        } else {
            w = firstLetter + w.slice(1);
        }

        words[a] = w;
    }
    return words.join(" ");
}

function primeiraLetraMaiusculaTexto(text) {
    var loweredText = text.toLowerCase();
    var words = loweredText.split(" ");
        var w = words[0];

        var firstLetter = w[0];

        if (w.length > 2) {
            w = firstLetter.toUpperCase() + w.slice(1);
        } else {
            w = firstLetter + w.slice(1);
        }

        words[0] = w;
    return words.join(" ");
}

function minuscula(texto) {
    return texto.toLowerCase();
}

function maiuscula(texto) {
    return texto.toUpperCase();
}

function copyToClipboard(url) {

    var success   = true,
        range     = document.createRange(),
        selection;

    // For IE.
    if (window.clipboardData) {
        window.clipboardData.setData("Text", url);
    } else {
        // Create a temporary element off screen.
        var tmpElem = $('<div>');
        tmpElem.css({
            position: "absolute",
            left:     "-1000px",
            top:      "-1000px",
        });
        // Add the input value to the temp element.
        tmpElem.text(url);
        $("body").append(tmpElem);
        // Select temp element.
        range.selectNodeContents(tmpElem.get(0));
        selection = window.getSelection ();
        selection.removeAllRanges ();
        selection.addRange (range);
        // Lets copy.
        try {
            success = document.execCommand ("copy", false, null);
        }
        catch (e) {
            iziToast.error({title: 'Erro!', message: 'Erro ao copiar!', position: 'bottomRight'});

        }
        if (success) {
            iziToast.success({title: 'Copiado!', message: 'Item copiado!', position: 'bottomRight'});
            // remove temp element.
            tmpElem.remove();
        }
    }
}

function compartilhar(titulo, texto, url) {
    const shareData = {
        title: titulo,
        text: texto,
        url: url,
    };

    if (navigator.share) {
        iziToast.info({title: 'Aguarde...', message: '', position: 'bottomRight'});
        navigator.share(shareData)
            .then(() => console.log('Successful share'))
            .catch((error) => console.log('Error sharing', error));
    } else {
        //$("#modalCompartilhar").modal({show: true});
        iziToast.warning({title: 'Atenção!', message: 'Dispositivo incompatível. :(', position: 'bottomRight'});
    }
}

function detectar_mobile() {
    var check = false; //wrapper no check
    (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))check = true})(navigator.userAgent||navigator.vendor||window.opera);
    return check;
}

function enviaMensagemWhatsApp(url) {
    encodeURIComponent(url);
    if (detectar_mobile()) {
        //window.open("https://api.whatsapp.com/send?phone=+55" + url)
        window.open("whatsapp://send?phone=55" + url)
    } else {
        window.open("https://web.whatsapp.com/send?phone=55" + url)
    }
}

// COMPARTILHAR NO WHATSAPP
//<a href="whatsapp://send?text=The text to share!" data-action="share/whatsapp/share">Share via Whatsapp</a>
function compartilharMensagemWhatsApp(url) {
    encodeURIComponent(texto);
    if (detectar_mobile()) {
        //window.open("https://api.whatsapp.com/send?phone=+55" + url)
        window.open("whatsapp://send?text=" + texto)
    } else {
        //window.open("https://web.whatsapp.com/send?phone=55" + url)
    }
}

function antiXss(s) {
    return s.replace(/./g, function(x) {
        return { '<': '&lt;', '>': '&gt;', '&': '&amp;', '"': '&quot;', "'": '&#39;' }[x] || x;
    });
}

// Add hash to the URL on open modal event
$('.modal').on('shown.bs.modal', function() {
    if (typeof(this.dataset.hash) !== 'undefined') {
        history.pushState(null, null, this.dataset.hash)
    }
});

$('.modal').on('hide.bs.modal', function(event) {
    if (this.dataset.pushback !== 'true') {
        event.preventDefault()
        history.back()
    }
    this.dataset.pushback = ''
});

window.onpopstate = function(event) {
    let open_modal = document.querySelector('.modal.show')
    if (open_modal) {
        open_modal.dataset.pushback = 'true';
        $(open_modal).modal('hide')
    }
}

function getLocation() {
    if (navigator.geolocation)
    {
        navigator.geolocation.getCurrentPosition(showPosition,showErrorLocation, {maximumAge: 50000, timeout: 20000, enableHighAccuracy: true});
    }
    else{
        iziToast.warning({title: 'Erro!', message: "Seu browser não suporta Geolocalização.", position: 'bottomRight'});
    }
}

function showErrorLocation(error) {
    switch(error.code)
    {
        case error.PERMISSION_DENIED:
            iziToast.warning({title: 'Atenção', message: "Usuário rejeitou a solicitação de Geolocalização.", position: 'bottomRight'});
            break;
        case error.POSITION_UNAVAILABLE:
            iziToast.warning({title: 'Atenção', message: "Localização indisponível.", position: 'bottomRight'});
            break;
        case error.TIMEOUT:
            iziToast.warning({title: 'Atenção', message: "A requisição expirou.", position: 'bottomRight'});
            console.log("A requisição expirou.")
            break;
        case error.UNKNOWN_ERROR:
            iziToast.warning({title: 'Atenção', message: "Tente novamente mais tarde!", position: 'bottomRight'});
            break;
    }
}

function showModal(modal1, modal2) {

    if (modal1!==null){
        window.history.back();
    }

    if (modal2!==null){
        $(modal2).modal({show: true});
    }

}

function getHourUser() {
    var dNow = new Date();
    var localdate = dNow.getHours() + ':' + dNow.getMinutes();
    return localdate;
}

function getDateHourUser(){
    var dNow = new Date();
    var localdate = dNow.getDate().toString().padStart(2, '0') + '/' + (dNow.getMonth()+1).toString().padStart(2, '0') + '/' + dNow.getFullYear()+' '+dNow.getHours().toString().padStart(2, '0') + ':' + dNow.getMinutes().toString().padStart(2, '0');
    return localdate;
}

function getDateUser(){
    var dNow = new Date();
    var localdate = dNow.getDate().toString().padStart(2, '0') + '/' + (dNow.getMonth()+1).toString().padStart(2, '0') + '/' + dNow.getFullYear();
    return localdate;
}

function scrollItem(item) {
    $('html, body').animate({
        scrollTop: $("#"+item).offset().top-90
    }, 1000);
}

function scrollItemCenter(item) {
    $('html, body').animate({
        scrollTop: $("#"+item).offset().top-150
    }, 1000);
}

function clickProgress(elemento, destino = "#", css = "") {
    document.getElementById(elemento).innerHTML = '<span class="spinner-border spinner-border-sm '+css+'" role="status" aria-hidden="true"></span>';

    if (destino!=null)
        window.location.href = destino
}

async function clickBotaoProgressAtivo(elemento, destino = null, css = null) {
    var temp = document.getElementById(elemento).innerHTML;
    if (css!==null){
        document.getElementById(elemento).innerHTML = '<span class="spinner-border spinner-border-sm'+css+'" role="status" aria-hidden="true"></span>';
        document.getElementById(elemento).disabled = true;
    } else {
        document.getElementById(elemento).innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        document.getElementById(elemento).disabled = true;
    }

    if (destino!=null)
        window.location.href = destino
    else
        return temp
}

function clickBotaoProgressInativo(elemento, texto) {
    document.getElementById(elemento).innerHTML = texto;
    document.getElementById(elemento).disabled = false;
}

function downloadImagetToBlob(url,divdestino){
    jQuery.ajax({
        url:'url',
        cache:false,
        xhr:function(){// Seems like the only way to get access to the xhr object
            var xhr = new XMLHttpRequest();
            xhr.responseType= 'blob'
            return xhr;
        },
        success: function(data){
            var url = window.URL || window.webkitURL;
            var img = $('<img id="produto">');
            img.attr('src', url.createObjectURL(data));
            img.appendTo('#'+divdestino);
            console.log(data);
        },
        error:function(){

        }
    });
}

// VALIDAÇÃO DE CPF (TESTA O DÍGITO VERIFICADOR)
function validaCPF(cpf) {
    var sum = 0;
    var remainder;

    cpf = cpf.replace('.', '')
        .replace('.', '')
        .replace('-', '')
        .trim();

    var allEqual = true;
    for (var i = 0; i < cpf.length - 1; i++) {
        if (cpf[i] != cpf[i + 1])
            allEqual = false;
    }
    if (allEqual)
        return false;

    for (i = 1; i <= 9; i++)
        sum = sum + parseInt(cpf.substring(i - 1, i)) * (11 - i);
    remainder = (sum * 10) % 11;

    if ((remainder == 10) || (remainder == 11))
        remainder = 0;
    if (remainder != parseInt(cpf.substring(9, 10)))
        return false;

    sum = 0;
    for (i = 1; i <= 10; i++)
        sum = sum + parseInt(cpf.substring(i - 1, i)) * (12 - i); remainder = (sum * 10) % 11;

    if ((remainder == 10) || (remainder == 11))
        remainder = 0;
    if (remainder != parseInt(cpf.substring(10, 11)))
        return false;

    return true;
}

//valida o CNPJ digitado
function validaCNPJ(cnpj){
    //var cnpj = ObjCnpj.value;
    var valida = new Array(6,5,4,3,2,9,8,7,6,5,4,3,2);
    var dig1= new Number;
    var dig2= new Number;

    exp = /\.|\-|\//g;
    cnpj = cnpj.toString().replace( exp, "" );
    var digito = new Number(eval(cnpj.charAt(12)+cnpj.charAt(13)));

    for(i = 0; i<valida.length; i++){
        dig1 += (i>0? (cnpj.charAt(i-1)*valida[i]):0);
        dig2 += cnpj.charAt(i)*valida[i];
    }
    dig1 = (((dig1%11)<2)? 0:(11-(dig1%11)));
    dig2 = (((dig2%11)<2)? 0:(11-(dig2%11)));

    if(((dig1*10)+dig2) != digito)
        return false;

    return true;
}

async function alertSuccess(mensagem){
    iziToast.success({title: 'Ok!', message: mensagem, position: 'bottomRight'});
}

async function alertError(mensagem){
    iziToast.error({title: 'Ops!', message: mensagem, position: 'bottomRight'});
}

async function alertWarning(mensagem){
    iziToast.warning({title: 'Atenção!', message: mensagem, position: 'bottomRight'});
}

async function alertInfo(mensagem){
    iziToast.info({title: 'Informaçao!', message: mensagem, position: 'bottomRight'});
}

function alertModal(mensagem, tipo = 'warning', titulo = 'Atenção!', btnText = 'Ok', btnColor = '#9400d3') {
    Swal.fire({
        title: titulo,
        icon: tipo, // Pode ser: 'warning', 'error', 'success', 'info', 'question'
        html: mensagem,
        showCloseButton: true,
        showCancelButton: false,
        focusConfirm: true,
        confirmButtonText: btnText,
        confirmButtonAriaLabel: 'sim',
        confirmButtonColor: btnColor,
        customClass: {
            htmlContainer: 'text-start'
        }
    }).then((result) => {})
}

/**
 * Função para ocutar a modal
 * @param nome da modal
 * @returns {Promise<void>}
 */
async function hideModal(id) {
    var modalInstance = bootstrap.Modal.getInstance(document.getElementById(id));
    modalInstance.hide();
}

/**
 * Função para mostrar a modal com timeout personalizavel
 * @param nome da modal
 * @param timeout de espera para mostrar
 * @returns {Promise<void>}
 */
async function showModal(nome, timeout= 0) {
    await setTimeout(function () {
        (new bootstrap.Modal(document.getElementById(nome), {
            // keyboard: false
        })).show();
    }, timeout);
}

function w3AddClass(element, name) {
    var i, arr1, arr2;
    arr1 = element.className.split(" ");
    arr2 = name.split(" ");
    for (i = 0; i < arr2.length; i++) {
        if (arr1.indexOf(arr2[i]) == -1) {
            element.className += " " + arr2[i];
        }
    }
}

function w3RemoveClass(element, name) {
    var i, arr1, arr2;
    arr1 = element.className.split(" ");
    arr2 = name.split(" ");
    for (i = 0; i < arr2.length; i++) {
        while (arr1.indexOf(arr2[i]) > -1) {
            arr1.splice(arr1.indexOf(arr2[i]), 1);
        }
    }
    element.className = arr1.join(" ");
}

// Add active class to the current button (highlight it)
var btnContainer = document.getElementById("myBtnContainer");
if (btnContainer != null) {
    var btns = btnContainer.getElementsByClassName("btn");
    for (var i = 0; i < btns.length; i++) {
        btns[i].addEventListener("click", function () {
            var current = document.getElementsByClassName("active");
            current[0].className = current[0].className.replace(" active", "");
            this.className += " active";
        });
    }
}

function getCookie(name) {
    var cookies = document.cookie;
    var prefix = name + "=";
    var begin = cookies.indexOf("; " + prefix);

    if (begin == -1) {

        begin = cookies.indexOf(prefix);

        if (begin != 0) {
            return '';
        }

    } else {
        begin += 2;
    }

    var end = cookies.indexOf(";", begin);

    if (end == -1) {
        end = cookies.length;
    }

    return unescape(cookies.substring(begin + prefix.length, end));
}

function setCookie(name, value, durationDay=365) {
    var date = new Date();
    date.setTime(date.getTime()+(durationDay*24*60*60*1000));
    var cookie = name + "=" + escape(value) + "; path=/; expires=" + date.toGMTString()+"; SameSite=None; Secure; domain="+window.location.host;

    document.cookie = cookie;
}

function deleteCookie(name) {
    if (getCookie(name)) {
        document.cookie = name + "=" + "; expires=Thu, 01-Jan-70 00:00:01 GMT";
    }
}

function validaForm() {
    var forms = document.getElementsByClassName("needs-validation");
    var validation = Array.prototype.filter.call(forms, function (form) {

        if (form.checkValidity() === false) {
            //  input.classList.add('invalid-feedback');
            event.preventDefault();
            event.stopPropagation();
        } else {
            //input.classList.add('valid-feedback');
        }
        form.classList.add('was-validated');
    });
}

function validaFormId(form) {
    if (document.getElementById(form).checkValidity() === false) {
        //  input.classList.add('invalid-feedback');
        event.preventDefault();
        event.stopPropagation();
    } else {
        //input.classList.add('valid-feedback');
    }
    document.getElementById(form).classList.add('was-validated');
}

function removeValidaForm(){
    var forms = document.getElementsByClassName("needs-validation");
    var validation = Array.prototype.filter.call(forms, function (form) {
        form.classList.remove("was-validated");
    });
}

function submitForm(nameButton, nameForm) {
    let textButton = document.getElementById(nameButton).innerHTML;
    clickBotaoProgressAtivo(nameButton);
    validaForm();
    if (document.getElementById (nameForm).checkValidity ()) {
        document.getElementById(nameForm).submit();
    } else {
        clickBotaoProgressInativo (nameButton, textButton);
        alertError("Preencha todos os campos corretamente!")
    }
}

function formatMoedaBrasil(value, cifrao=false){
    if (cifrao)
        return value.toLocaleString('pt-br',{style: 'currency', currency: 'BRL'});
    else
        return value.toLocaleString('pt-br', {minimumFractionDigits: 2});
}

function formatNumberBrasil(value){
    return value.toLocaleString('pt-br');
}

function adicionaZero(numero){
    if (numero <= 9)
        return "0" + numero;
    else
        return numero;
}

function getPassword(size=8, number=true, string=true, caracter=false) {
    var number = number?'0123456789':'';
    var string = string?'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLMNOPQRSTUVWXYZ':'';
    var caracter = caracter?'@#$%^&*()+?><:{}[]':'';
    var chars = number+string+caracter;
    var passwordLength = size;
    var password = "";

    for (var i = 0; i < passwordLength; i++) {
        var randomNumber = Math.floor(Math.random() * chars.length);
        password += chars.substring(randomNumber, randomNumber + 1);
    }
    return password;
}

// FUNÇÃO PARA EXECUTAR DEPOIS DE CARREGAR A PAGINA
function startPage(func, evnt="load", elem=window) {
    if (elem.addEventListener)  // W3C DOM
        elem.addEventListener(evnt, func, false);
    else if (elem.attachEvent) { // IE DOM
        var r = elem.attachEvent("on" + evnt, func);
        return r;
    } else window.alert('I\'m sorry Dave, I\'m afraid I can\'t do that.');
}

$(document).ready(function() {
    $('.select2').select2({
        language: "pt-BR",
        placeholder: " Selecione ",
        allowClear: true,
        theme: 'bootstrap-5'
    });
});

function updateSelect2(placeholder = " Selecione "){
    $('.select2').select2({
        language: "pt-BR",
        placeholder: placeholder,
        allowClear: true,
        theme: 'bootstrap-5'
    });
}

function modalTemCerteza(formId){
    Swal.fire({
        title: 'Atenção',
        icon: 'warning',
        html: 'Tem certeza que deseja executar essa ação!',
        showCloseButton: true,
        showCancelButton: true,
        focusConfirm: true,
        cancelButtonText: 'Não',
        cancelButtonAriaLabel: 'nao',
        confirmButtonText: 'Sim',
        confirmButtonAriaLabel: 'sim',
        confirmButtonColor: '#9400d3',
    }).then((result) => {
        if (result.isConfirmed) {
            validaForm()
            if (document.getElementById(formId).checkValidity()){
                document.getElementById(formId).submit();
            } else {
                alertError("Preencha todos os campos corretamente");
            }
        }
    })
}

function verificaModeDark() {
    let theme = getCookie("theme");
    let checkbox = document.getElementById("checkboxModeDark");

    if (!checkbox) return; // Evita erro se o checkbox não existir

    checkbox.checked = theme === "dark";
}

function convertDateToISO(dateStr) {
    // Expects dateStr in format d/m/Y
    const [day, month, year] = dateStr.split('/');
    return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
}

function convertIsoToDate(isoStr) {
    // Espera o formato YYYY-MM-DD
    const [year, month, day] = isoStr.split('-');
    return `${day.padStart(2, '0')}/${month.padStart(2, '0')}/${year}`;
}

function validaDataNascimento(bDayIso, ageMin = 18, ageMax = 130) {
    // comparo as datas e calculo a idade
    var hoje = new Date();
    var nasc = new Date(bDayIso);
    var idade = hoje.getFullYear() - nasc.getFullYear();
    var m = hoje.getMonth() - nasc.getMonth();
    if (m < 0 || (m === 0 && hoje.getDate() < nasc.getDate())) idade--;

    if (idade < ageMin) {
        return false;
    }

    if (idade >= ageMin && idade <= ageMax) {
        return true;
    }
    return false;
}

