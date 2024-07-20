document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('[data-action="delete"]').forEach((element) => {
        element.addEventListener("click", (event) => {
            event.preventDefault();
            if (confirm("Are you sure you want to delete this item?")) {
                const deleteForm = document.getElementById("delete-form");
                deleteForm.action = element.dataset.resource;
                console.log(deleteForm, element);
            }
        });
    });

    // document.querySelectorAll('[data-action="edit"]').forEach((element) => {
    //     element.addEventListener("click", (event) => {
    //         event.preventDefault();
    //         if (confirm("Are you sure you want to edit this item?")) {
    //             console.log("true");
    //             // document.getElementById("delete-form").submit();
    //         }
    //     });
    // });
});
