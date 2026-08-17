document.addEventListener('DOMContentLoaded', function() {
  
  // ==========================
  // 1. ALERT MODAL & CREATION
  // ==========================
  const alertModal = document.querySelector("#alertModal");
  if (alertModal) {
    const closeAlertModal = () => {
      alertModal.style.display = "none";
      const form = document.querySelector("#alertForm");
      if (form) form.reset();
    };

    document.querySelectorAll("[data-open-modal]").forEach(b => {
      b.addEventListener("click", (e) => {
        e.preventDefault();
        alertModal.style.display = "flex";
        setTimeout(() => {
          const titleInput = document.querySelector("#alertTitle");
          if (titleInput) titleInput.focus();
        }, 100);
      });
    });

    document.querySelector("#closeAlertModal")?.addEventListener("click", closeAlertModal);
    document.querySelector("#cancelAlertModal")?.addEventListener("click", closeAlertModal);
    alertModal.addEventListener("click", e => { if (e.target === alertModal) closeAlertModal(); });

    document.querySelector("#alertForm")?.addEventListener("submit", function(e) {
      e.preventDefault();
      const submitBtn = document.querySelector("#submitAlertBtn");
      const btnText = document.querySelector("#submitAlertBtnText") || submitBtn;
      const originalText = btnText ? btnText.textContent : "Create";
      if (btnText) btnText.textContent = (window._lang && window._lang.creating) ? window._lang.creating : "Creating...";
      if (submitBtn) submitBtn.disabled = true;

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
          if (btnText) btnText.textContent = originalText;
          if (submitBtn) submitBtn.disabled = false;
        }
      })
      .catch(err => {
        console.error(err);
        alert("An error occurred while creating alert");
        if (btnText) btnText.textContent = originalText;
        if (submitBtn) submitBtn.disabled = false;
      });
    });
  }

  // ==========================
  // 2. ALERT STATUS & ACTIONS
  // ==========================
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

  // Move to Trash
  document.querySelectorAll("[data-trash]").forEach(btn => {
    btn.addEventListener("click", function(e) {
      e.preventDefault();
      e.stopPropagation();
      const id = this.dataset.trash;
      if (confirm("Move this alert to trash?")) {
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

  // Restore from Trash
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

  // Delete Permanently
  document.querySelectorAll("[data-delete], [data-delete-permanent]").forEach(btn => {
    btn.addEventListener("click", function(e) {
      e.preventDefault();
      e.stopPropagation();
      const id = this.dataset.delete || this.dataset.deletePermanent;
      if (confirm("Delete this alert permanently?")) {
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

  // Empty Trash
  document.querySelector("#emptyTrashBtn")?.addEventListener("click", function(e) {
    e.preventDefault();
    if (confirm("Are you sure you want to permanently delete all items in trash?")) {
      fetch("../actions/alert_action.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "action=empty_trash"
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) window.location.reload();
      });
    }
  });

  // ==========================
  // 3. NOTE MODAL & ACTIONS
  // ==========================
  const noteModal = document.querySelector("#noteModal");
  if (noteModal) {
    const closeNoteModal = () => {
      noteModal.style.display = "none";
      document.querySelector("#noteForm")?.reset();
    };

    const openNote = (e) => {
      if (e) e.preventDefault();
      noteModal.style.display = "flex";
      setTimeout(() => document.querySelector("#noteTitle")?.focus(), 100);
    };

    document.querySelector("#openNoteModal")?.addEventListener("click", openNote);
    document.querySelector("#openNoteModal2")?.addEventListener("click", openNote);
    document.querySelector("#closeNoteModal")?.addEventListener("click", closeNoteModal);
    document.querySelector("#closeNoteModal2")?.addEventListener("click", closeNoteModal);
    noteModal.addEventListener("click", e => { if (e.target === noteModal) closeNoteModal(); });

    document.querySelector("#noteForm")?.addEventListener("submit", function(e) {
      e.preventDefault();
      const formData = new URLSearchParams(new FormData(this));
      formData.append("action", "create");

      fetch("../actions/note_action.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: formData.toString()
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          window.location.reload();
        } else {
          alert(data.error || "Failed to create note");
        }
      });
    });

    document.querySelectorAll(".delete-note-btn, [data-note-id]").forEach(btn => {
      btn.addEventListener("click", function(e) {
        e.preventDefault();
        e.stopPropagation();
        const id = this.dataset.noteId;
        if (confirm("Delete this note?")) {
          fetch("../actions/note_action.php", {
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
  }

  // ==========================
  // 4. SETTINGS DANGER ZONE
  // ==========================
  document.querySelector("#clearCompletedBtn")?.addEventListener("click", function(e) {
    e.preventDefault();
    if (confirm("Clear all completed alerts?")) {
      fetch("../actions/alert_action.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "action=clear_completed"
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          alert("Completed alerts cleared.");
          window.location.reload();
        }
      });
    }
  });

  document.querySelector("#resetAllBtn")?.addEventListener("click", function(e) {
    e.preventDefault();
    if (confirm("WARNING: This will permanently delete ALL your alerts and notes. Are you sure?")) {
      fetch("../actions/alert_action.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "action=reset_all"
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          alert("All data reset.");
          window.location.reload();
        }
      });
    }
  });

});
