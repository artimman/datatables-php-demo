/**
 * DbM DataTables PHP, file: delete-record.js
 * 
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 */

document.addEventListener("DOMContentLoaded", () => {
  const deleteModal = document.getElementById("deleteModal");
  const deleteButton = document.getElementById("recordDelete");

  // Kliknięcie w dropdown -> otwarcie modala
  document.body.addEventListener("click", (e) => {
    const btn = e.target.closest(".deleteRecord");
    if (!btn) return;
    new bootstrap.Modal(deleteModal).show();
  });

  // Kliknięcie "Usuń" w modalu -> wywołanie API
  deleteButton.addEventListener("click", () => {
    // Tu można dopisać usuwanie rekordu
    const modalInstance = bootstrap.Modal.getInstance(deleteModal);
    if (modalInstance) modalInstance.hide();
    console.log("Not available in PHP module.");
  });
});
