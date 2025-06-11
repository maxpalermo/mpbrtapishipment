document.addEventListener("DOMContentLoaded", async (e) => {
    const printBorderoBtn = document.getElementById("page-header-desc-brt_shipment_bordero-print");
    if (printBorderoBtn) {
        printBorderoBtn.addEventListener("click", async (e) => {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            const confirm = await swalConfirm("Stampare il borderò?");
            if (!confirm) {
                return false;
            }

            const href = printBorderoBtn.getAttribute("href");
            if (!href) {
                return false;
            }
            //apro una nuova finestra
            window.open(href, "_blank");
        });
    }
});
