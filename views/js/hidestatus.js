console.log("HideStatus-Modul aktiv");

function hideMatchingElements(container) {
  if (typeof hidestatus_terms === 'undefined') return;

  const terms = hidestatus_terms.map(term => term.toLowerCase().trim());

  container.querySelectorAll('li, option').forEach(el => {
    const text = el.textContent.toLowerCase().trim();
    if (terms.some(term => text.includes(term))) {
      console.log("Verstecke:", text);
      el.style.display = 'none';
    }
  });
}

function applyHideStatusLogic() {
  const url = window.location.href;

  const isDetailView = url.includes('/sell/orders/') && url.includes('/view');
  //const isOverview = url.includes('/sell/orders/') && !url.includes('/view');

  if (isDetailView) {

    hideMatchingElements(document);

    const observer = new MutationObserver(mutations => {
      mutations.forEach(mutation => {
        mutation.addedNodes.forEach(node => {
          if (node.nodeType === 1) {
            hideMatchingElements(node);
          }
        });
      });
    });

    observer.observe(document.body, { childList: true, subtree: true });
  }
}

document.addEventListener('DOMContentLoaded', applyHideStatusLogic);

