// Admin Panel JavaScript

function showToast(message, type) {
  const container = document.getElementById("toast-container");
  if (!container || !message) return;

  const toast = document.createElement("div");
  toast.className = `toast toast-${type}`;
  toast.setAttribute("role", "alert");

  const iconClass = type === "success" ? "fa-check-circle" : "fa-exclamation-circle";

  toast.innerHTML = `
    <i class="fas ${iconClass} toast-icon"></i>
    <span class="toast-message"></span>
    <button type="button" class="toast-close" aria-label="Close">&times;</button>
  `;

  toast.querySelector(".toast-message").textContent = message;
  container.appendChild(toast);

  requestAnimationFrame(() => toast.classList.add("show"));

  const dismiss = () => {
    toast.classList.remove("show");
    toast.classList.add("hide");
    setTimeout(() => toast.remove(), 300);
  };

  toast.querySelector(".toast-close").addEventListener("click", dismiss);
  setTimeout(dismiss, 4000);
}

document.addEventListener("DOMContentLoaded", function () {
  if (window.flashMessages) {
    if (window.flashMessages.success) {
      showToast(window.flashMessages.success, "success");
    }
    if (window.flashMessages.error) {
      showToast(window.flashMessages.error, "error");
    }
  }
  // Create sidebar overlay if it doesn't exist
  let overlay = document.querySelector(".sidebar-overlay");
  if (!overlay) {
    overlay = document.createElement("div");
    overlay.className = "sidebar-overlay";
    document.body.appendChild(overlay);
  }

  // Toggle sidebar on mobile
  const toggleBtn = document.querySelector(".sidebar-toggle");
  const sidebar = document.querySelector(".sidebar");
  const mainContent = document.querySelector(".main-content");

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      sidebar.classList.toggle("active");
      overlay.classList.toggle("active");
      toggleBtn.classList.toggle("active");
    });

    // Close sidebar when clicking overlay
    overlay.addEventListener("click", function () {
      sidebar.classList.remove("active");
      overlay.classList.remove("active");
      toggleBtn.classList.remove("active");
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener("click", function (e) {
      if (window.innerWidth <= 768) {
        if (
          sidebar.classList.contains("active") &&
          !sidebar.contains(e.target) &&
          !toggleBtn.contains(e.target)
        ) {
          sidebar.classList.remove("active");
          overlay.classList.remove("active");
          toggleBtn.classList.remove("active");
        }
      }
    });

    // Close sidebar on window resize if it becomes desktop view
    window.addEventListener("resize", function () {
      if (window.innerWidth > 768) {
        sidebar.classList.remove("active");
        overlay.classList.remove("active");
        toggleBtn.classList.remove("active");
      }
    });
  }

  // Add data-label attributes to table cells for mobile responsive view
  const tables = document.querySelectorAll(".data-table");
  tables.forEach((table) => {
    const headers = table.querySelectorAll("thead th");
    const rows = table.querySelectorAll("tbody tr");

    rows.forEach((row) => {
      const cells = row.querySelectorAll("td");
      cells.forEach((cell, index) => {
        if (headers[index]) {
          cell.setAttribute("data-label", headers[index].textContent.trim());
        }
      });
    });
  });

  // Handle dropdowns
  const dropdownToggles = document.querySelectorAll(".dropdown-toggle");

  dropdownToggles.forEach((toggle) => {
    toggle.addEventListener("click", function (e) {
      e.stopPropagation();

      // Close any open dropdowns first
      document.querySelectorAll(".dropdown-menu.show").forEach((menu) => {
        if (menu !== this.nextElementSibling) {
          menu.classList.remove("show");
        }
      });

      // Toggle the clicked dropdown
      const dropdownMenu =
        this.closest(".dropdown").querySelector(".dropdown-menu");
      dropdownMenu.classList.toggle("show");
    });
  });

  // Close dropdowns when clicking outside
  document.addEventListener("click", function (e) {
    if (!e.target.closest(".dropdown")) {
      document.querySelectorAll(".dropdown-menu.show").forEach((menu) => {
        menu.classList.remove("show");
      });
    }
  });

  // Form submission handling
  const adminForm = document.getElementById("adminForm");
  if (adminForm) {
    adminForm.addEventListener("submit", function (event) {
      event.preventDefault();

      // For CKEditor, update the textarea with the editor content
      if (typeof CKEDITOR !== "undefined" && CKEDITOR.instances.bio) {
        CKEDITOR.instances.bio.updateElement();
      }

      // Show success message (in real app, you would submit the form data)
      const successMessage = document.createElement("div");
      successMessage.className = "alert alert-success";
      successMessage.textContent = "Form submitted successfully!";

      adminForm.prepend(successMessage);

      // Reset form (and CKEditor)
      adminForm.reset();
      if (typeof CKEDITOR !== "undefined" && CKEDITOR.instances.bio) {
        CKEDITOR.instances.bio.setData("");
      }

      // Remove success message after 3 seconds
      setTimeout(() => {
        successMessage.remove();
      }, 3000);
    });
  }

  // Login form handling
  const loginForm = document.getElementById("loginForm");
  if (loginForm) {
    loginForm.addEventListener("submit", function (event) {
      event.preventDefault();

      // Redirect to dashboard (in real app, you would authenticate)
      window.location.href = "dashboard.html";
    });
  }
});
