async function swalConfirm(message) {
    return new Promise((resolve, reject) => {
        Swal.fire({
            title: "Confirm",
            html: message,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Si",
            cancelButtonText: "Annulla",
            confirmButtonColor: "#25b9d7"
        }).then((result) => {
            if (result.isConfirmed) {
                resolve(true);
            } else {
                resolve(false);
            }
        });
    });
}
