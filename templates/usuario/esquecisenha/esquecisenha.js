/**
 * RECUPERAÇÃO DE SENHA
 */
async function recuperasenha() {

    var online = navigator.onLine;
    if (online) {
        validaForm()

        if (document.querySelector("#cpf").checkValidity() === true) {
            clickBotaoProgressAtivo("btnEsqueciSenhaEnviar");

            var cpf = document.getElementById("cpf").value;

            await requisicaoHttp("/api/recuperasenha", "POST", JSON.stringify({"cpf": cpf})).then((retorno) => {
                clickBotaoProgressInativo("btnEsqueciSenhaEnviar", "Entrar");
                document.getElementById("cpf").value = "";
                removeValidaForm()
                if (retorno.error) {
                    alertaErro(retorno.msg);
                } else {
                    alertaSucesso(retorno.msg);
                }
            }).catch(() => {
                clickBotaoProgressInativo("btnEsqueciSenhaEnviar", "Entrar");
                alertaErro("Ops! Tivemos um problema, tente novamente mais tarde");
            });
        }
    } else {
        alertaErro("Ops! Tivemos um problema, tente novamente mais tarde");

    }
}