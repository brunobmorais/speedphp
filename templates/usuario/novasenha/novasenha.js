function validarSenha() {
    senha1 = document.form.cdsenha.value;
    senha2 = document.form.cdrptsenha.value;

    if (senha1.length >= 6) {
        if (senha1 !== senha2)
            iziToast.error({title: 'Erro!', message: "Senhas não conferem", position: 'bottomRight'});
        else
            document.form.submit();
    } else {
        iziToast.error({title: 'Erro!', message: "Senhas muito pequena", position: 'bottomRight'});
    }
}