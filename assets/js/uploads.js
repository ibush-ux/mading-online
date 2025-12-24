const form = document.getElementById('uploadForm');
const resultDiv = document.getElementById('result');

form.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(form);

    try {
        const res = await fetch('http://localhost/mading-online/backend/api/upload.php', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();

        if (data.status === 'success') {
            resultDiv.innerHTML = `
                <p>Berhasil upload!</p>
                <p><a href="${data.file_url}" target="_blank">Lihat file</a></p>
            `;
        } else {
            resultDiv.innerHTML = `<p style="color:red;">Error: ${data.message}</p>`;
        }
    } catch (err) {
        resultDiv.innerHTML = `<p style="color:red;">Error: ${err}</p>`;
    }
});

