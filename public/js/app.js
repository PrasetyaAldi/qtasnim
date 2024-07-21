document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('[data-action="delete"]').forEach((element) => {
        element.addEventListener("click", (event) => {
            event.preventDefault();
            if (confirm("Are you sure you want to delete this item?")) {
                const deleteForm = document.getElementById("delete-form");
                deleteForm.action = element.dataset.resource;
            }
        });
    });
});
