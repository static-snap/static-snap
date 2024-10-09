/* document.addEventListener('DOMContentLoaded', function () {
  const $searchInputs = document.querySelectorAll(
    '.static-snap-search-form .static-snap-search-form-container input'
  );

  const $results = document.querySelectorAll('.static-snap-search-results');

  if (!$searchInputs) {
    return;
  }

  function bindSearch(searchInput) {
    searchInput.addEventListener('input', function (e) {
      const searchValue = e.target.value;
      StaticSnapFrontend.Search.search(searchValue).then((results) => {
        for (let i = 0; i < $results.length; i++) {
          const result = $results[i];
          StaticSnapFrontend.Search.renderResults(result, results);
        }
      });
    });
  }

  for (let i = 0; i < $searchInputs.length; i++) {
    bindSearch($searchInputs[i]);
  }
});
 */
