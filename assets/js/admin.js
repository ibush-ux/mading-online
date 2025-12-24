document.getElementById("loginForm").addEventListener("submit", async function (e) {
    e.preventDefault();

    const username = document.getElementById("username").value.trim();
    const password = document.getElementById("password").value.trim();

    try {
        const response = await fetch("../backend/auth/login.php", {
        method: "POST",
        headers: {
        "Content-Type": "application/json"
        },
        body: JSON.stringify({
        username,
        password
        })
    });


        const text = await response.text();
        console.log("RAW RESPONSE:", text);

        const data = JSON.parse(text);
        console.log("PARSED:", data);

        if (data.success) {
            localStorage.setItem("token", data.token);
            window.location.href = "index.html";
        } else {
            alert("Login gagal: " + data.message);
        }

    } catch (err) {
        console.error("ERROR:", err);
        alert("Terjadi kesalahan, cek console.");
    }
});
