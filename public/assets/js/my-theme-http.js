async function requisicaoHttp(url, metodo = 'GET', body = null, headers = {'Content-Type': 'application/json'}, debug = false) {

    try {
        // Detecta se é uma URL externa
        let isExternalUrl = ! url.startsWith(window.location.origin) && !url.startsWith('/');

        let fetchOptions = {
            method:  metodo,
            body: body,
            headers: headers
        };

        // Só inclui credentials para URLs do mesmo domínio
        if (!isExternalUrl) {
            fetchOptions.credentials = 'include';
        }

        let resp = await fetch(url, fetchOptions).catch(error => {
            if (debug)
                console.error('Aconteceu um erro:', error);
            return false;
        });

        if (debug)
            console.log(resp)

        if (!resp.ok)
            return false;

        let data = await resp.json();

        if (!data || data.length === 0)
            return false

        return data;

    } catch (err) {
        if (debug)
            console.error('Aconteceu um erro:', err.message);
        return false;
    }
}