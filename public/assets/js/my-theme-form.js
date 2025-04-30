// VALIDACAO BOOTSTRAP
(() => {
    'use strict'

    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    const forms = document.querySelectorAll('.needs-validation')

    // Loop over them and prevent submission
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }

            form.classList.add('was-validated')
        }, false)
    })
})();
// FIM VALIDACAO BOOTSTRAP

// FORM SUBMIT
// FORM SUBMIT
document.querySelectorAll("form").forEach((formElement) => {
    formElement.addEventListener("submit", (event) => {
        event.preventDefault();

        const submitter = event.submitter;
        const buttonId = submitter?.id || null;
        const formId = formElement.id;
        const textButton = submitter?.innerHTML || "";

        if (buttonId) clickBotaoProgressAtivo(buttonId);
        validaFormId(formId);

        if (formElement.checkValidity()) {
            window.onpageshow = function (event) {
                if (event.persisted && buttonId) {
                    clickBotaoProgressInativo(buttonId, textButton);
                }
            };

            formElement.submit(); // tudo certo, envia o formulário
        } else {
            if (buttonId) clickBotaoProgressInativo(buttonId, textButton);
            alertError("Preencha todos os campos corretamente!");
        }
    });
});
// FIM FORM SUBMIT

async function formToMap(formData){
    const value = Object.fromEntries(formData.entries());
    return value;
}