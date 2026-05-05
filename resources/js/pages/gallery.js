import GLightbox from "glightbox";
import Shuffle from "shufflejs";

function initGalleryFilter() {
  const galleryGrid = document.querySelector("[data-gallery-grid]");
  const filterOptions = document.querySelector(".filter-options");

  if (!galleryGrid || !filterOptions) {
    return;
  }

  const shuffle = new Shuffle(galleryGrid, {
    itemSelector: ".picture-item",
  });

  filterOptions.querySelectorAll("[data-group]").forEach((button) => {
    button.addEventListener("click", () => {
      const group = button.dataset.group ?? "all";

      filterOptions.querySelectorAll("[data-group]").forEach((filterButton) => {
        filterButton.classList.remove("active", "btn-primary");
        filterButton.classList.add("btn-outline-primary");
      });

      button.classList.add("active", "btn-primary");
      button.classList.remove("btn-outline-primary");

      shuffle.filter(group === "all" ? Shuffle.ALL_ITEMS : group);
    });
  });
}

function init() {
  GLightbox({
    selector: ".image-popup",
    title: false,
  });

  initGalleryFilter();
}

init();
