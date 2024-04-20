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
let form = document.querySelector("form");

if (form) {
    form.addEventListener("submit", (event) => {
        let submitter = event.submitter;
        let button = submitter.id;
        let form = event.target.id

        let textButton = document.getElementById(button).innerHTML;
        clickBotaoProgressAtivo(button);
        validaForm();
        if (document.getElementById(form).checkValidity() && event.returnValue) {
            (function () {
                window.onpageshow = function (event) {
                    if (event.persisted) {
                        clickBotaoProgressInativo(button, textButton);
                    }
                };
            })();

            return true
        }

        event.preventDefault();
        clickBotaoProgressInativo(button, textButton);
        alertError("Preencha todos os campos corretamente!")
        return false

    });
}
// FIM FORM SUBMIT