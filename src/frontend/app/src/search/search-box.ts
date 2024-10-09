import Constants from '../constants';
import { Search } from '../modules';

/**
 * This class is responsible for handling the search input and displaying the search results.
 * It listens to the input event on the input element and calls the search method of the Search module.
 * It then renders the results in the searchResultsSelector element.
 * If the searchResultsSelector element is not found, it creates a new div element next to the input element.
 * The search results are rendered in this new div element.
 */
export default class SearchBox {
  private selector: string;
  private searchResultsSelector: string;
  private elements: NodeListOf<Element> | null = null;
  private searchModule: Search;

  constructor(
    searchModule: Search,
    selector: string = Constants.defaultSearchInputSelector,
    searchResultsSelector: string = Constants.defaultSearchResultsSelector
  ) {
    this.selector = selector;
    this.searchResultsSelector = searchResultsSelector;
    this.searchModule = searchModule;
    document.addEventListener('DOMContentLoaded', this.initDomEvents.bind(this));
  }

  private initDomEvents = () => {
    this.elements = document.querySelectorAll(this.selector);
    // on press back button, get the value of q and search

    this.bindEvents();
  };

  public getElements = (): HTMLInputElement[] => {
    if (!this.elements) {
      return [];
    }
    // filter elements that are in wpadminbar
    return Array.from(this.elements).filter(
      (element) => !element.closest('#wpadminbar')
    ) as HTMLInputElement[];
  };

  public bindEvents = () => {
    const elements = this.getElements();
    elements &&
      elements.forEach((element) => {
        // ignore the search on wpadminbar

        // if element name=s change to q
        if (element.getAttribute('name') === 's') {
          element.setAttribute('name', 'q');
        }

        // if have a searchParams in the url, get the value of q and search
        const searchParams = new URLSearchParams(window.location.search);
        const query = searchParams.get('q');

        element.addEventListener('input', this.onSearch);
        if (query) {
          element.setAttribute('value', query);
          element.value = query;
          const event = new Event('input');
          element.dispatchEvent(event);
        }
        // disable submit on form
        element.closest('form')?.addEventListener('submit', (event) => {
          event.preventDefault();
        });

        window.addEventListener('popstate', this.onPopState);
      });
  };

  onPopState = (_event: PopStateEvent) => {
    const searchParams = new URLSearchParams(window.location.search);
    const query = searchParams.get('q');
    if (query) {
      this.getElements().forEach((element) => {
        element.setAttribute('value', query);
        element.value = query;
        const event = new Event('input');
        // if element has focus the value will not be updated

        element.blur();

        element.dispatchEvent(event);
      });
    }
  };

  onSearch = async (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (event.isTrusted) {
      // get input name and add searchParams to the url
      const searchParams = new URLSearchParams(window.location.search);
      searchParams.set('q', target.value);
      window.history.pushState(
        { q: target.value },
        '',
        `${window.location.pathname}?${searchParams.toString()}`
      );
    }

    let renderTarget =
      target.closest('search')?.querySelector(this.searchResultsSelector) ||
      target.closest('form')?.querySelector(this.searchResultsSelector) ||
      target.closest('div')?.querySelector(this.searchResultsSelector);

    if (!renderTarget) {
      // create a new div element next to the form of the input.
      renderTarget = document.createElement('div');
      renderTarget.className = 'static-snap-search-results';
      const parentSearch =
        target.closest('search') || target.closest('form') || target.closest('div');
      if (parentSearch) {
        parentSearch.appendChild(renderTarget);
      } else {
        console.error('No suitable parent found to append search results');
        return;
      }
    }

    const results = await this.searchModule.search(target.value);
    this.searchModule.renderResults(renderTarget as HTMLElement, results);
  };
}
