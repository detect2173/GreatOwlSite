// forms.js
document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form");

    if (!form) return;

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        // Simple validation (example)
        const name = form.querySelector("input[name='name']");
        const email = form.querySelector("input[name='email']");

        if (!name.value.trim()) {
            alert("Please enter your name");
            return;
        }
        if (!email.value.trim() || !email.value.includes("@")) {
            alert("Please enter a valid email");
            return;
        }

        // Submit via fetch (adjust URL to match your backend handler)
        fetch(form.action, {
            method: "POST",
            body: new FormData(form),
        })
            .then((res) => res.text())
            .then((data) => {
                alert("Form submitted successfully!");
                form.reset();
            })
            .catch((err) => {
                console.error(err);
                alert("There was an error submitting the form.");
            });
    });
});
