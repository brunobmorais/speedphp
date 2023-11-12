/**
 * LOGIN USUARIO
 */
function loginusuario() {

    var online = navigator.onLine;
    if (online) {
        validaForm()

        if (document.querySelector("#senha").checkValidity() === true && document.querySelector("#cpf").checkValidity() === true) {

            clickBotaoProgressAtivo("btnLoginEntrar");

            var cpf = document.getElementById("cpf").value;
            var senha = document.getElementById("senha").value;

            $.ajax({
                type: "POST",
                url: "/api/login",
                data: "cpf=" + cpf + "&senha=" + senha,
                dataType: "json"
            }).done(function (retorno) {
                if (!retorno.error) {
                    setCookie('token',retorno.token_user,0.5);
                    window.location.href = retorno.redireciona;
                } else {
                    clickBotaoProgressInativo("btnLoginEntrar","Entrar");
                    iziToast.error({title: 'Erro!', message: retorno.msg, position: 'bottomRight'});
                }
            }).fail(function (xhr, status, error) {
                clickBotaoProgressInativo("btnLoginEntrar","Entrar");
                iziToast.error({title: 'Erro!', message: 'Tente novamente mais tarde', position: 'bottomRight'});
            });
        }
    } else {
        clickBotaoProgressInativo("btnLoginEntrar","Entrar");
        iziToast.error({title: 'Erro!', message: 'Sem internet', position: 'bottomRight'});
    }
}