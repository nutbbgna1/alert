document.addEventListener('DOMContentLoaded', function() {
  // Modal Logic
  const backdrop = document.querySelector("#alertModal");
  if (backdrop) {
    const closeModal = () => {
      backdrop.style.display = "none";
      document.querySelector("#alertForm").reset();
    };

    document.querySelectorAll("[data-open-modal]").forEach(b => {
      b.addEventListener("click", () => {
        backdrop.style.display = "flex";
        setTimeout(() => document.querySelector("#alertTitle").focus(), 100);
      });
    });

    document.querySelector("#closeAlertModal")?.addEventListener("click", closeModal);
    document.querySelector("#cancelAlertModal")?.addEventListener("click", closeModal);
    backdrop.addEventListener("click", e => { if (e.target === backdrop) closeModal(); });

    document.querySelector("#alertForm")?.addEventListener("submit", function(e) {
      e.preventDefault();
      const btnText = document.querySelector("#submitAlertBtnText");
      const originalText = btnText.textContent;
      btnText.textContent = window._lang ? window._lang.creating : "Creating...";
      
      const formData = new URLSearchParams(new FormData(this));
      formData.append("action", "create");
      
      fetch("../actions/alert_action.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: formData.toString()
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          window.location.reload();
        } else {
          alert(data.error || "Failed to create alert");
          btnText.textContent = originalText;
        }
      }).catch(err => {
        console.error(err);
        alert("An error occurred");
        btnText.textContent = originalText;
      });
    });
  }

  // Toggle Complete
  document.querySelectorAll("[data-complete]").forEach(btn => {
    btn.addEventListener("click", function(e) {
      e.preventDefault();
      e.stopPropagation();
      const id = this.dataset.complete;
      fetch("../actions/alert_action.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=toggle_complete&id=${id}`
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) window.location.reload();
      });
    });
  });

  // Trash
  document.querySelectorAll("[data-trash]").forEach(btn => {
    btn.addEventListener("click", function(e) {
      e.preventDefault();
      e.stopPropagation();
      const id = this.dataset.trash;
      if(confirm("Move this alert to trash?")) {
        fetch("../actions/alert_action.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `action=trash&id=${id}`
        })
        .then(r => r.json())
        .then(data => {
          if (data.success) window.location.reload();
        });
      }
    });
  });

  // Delete permanently (Trash page)
  document.querySelectorAll("[data-delete-permanent]").forEach(btn => {
    btn.addEventListener("click", function(e) {
      e.preventDefault();
      e.stopPropagation();
      const id = this.dataset.deletePermanent;
      if(confirm("Delete this alert permanently?")) {
        fetch("../actions/alert_action.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `action=delete&id=${id}`
        })
        .then(r => r.json())
        .then(data => {
          if (data.success) window.location.reload();
        });
      }
    });
  });

  // Restore (Trash page)
  document.querySelectorAll("[data-restore]").forEach(btn => {
    btn.addEventListener("click", function(e) {
      e.preventDefault();
      e.stopPropagation();
      const id = this.dataset.restore;
      fetch("../actions/alert_action.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=restore&id=${id}`
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) window.location.reload();
      });
    });
  });
});
