async function requisicaoHttp(url, metodo = 'GET', body = null, headers = {'Content-Type': 'application/json'}, debug = false) {

    try {

        let resp = await fetch(url, {
            method: metodo,
            body: body,
            headers: headers
        }).catch(error =>{
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