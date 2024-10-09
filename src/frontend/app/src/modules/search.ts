import Constants from '../constants';
import Frontend from '../frontend';
import SearchModule from '../interfaces/search-module.interface';
import SearchBox from '../search/search-box';

export default class Search {
  private modules: SearchModule[] = [];
  private frontend: Frontend;
  private searchBox: SearchBox;
  constructor(frontend: Frontend) {
    this.frontend = frontend;

    const frontendSearchSettings = frontend.config().options?.search?.frontend_settings;
    this.searchBox = new SearchBox(
      this,
      frontendSearchSettings?.search_selector || Constants.defaultSearchInputSelector,
      frontendSearchSettings?.search_results_selector || Constants.defaultSearchResultsSelector
    );
  }

  public addModule(module: SearchModule) {
    this.modules.push(module);
  }

  private getSearchModule() {
    const search = this.frontend.config().options.search;

    const searchModuleType = search?.type || 'fuse-js';

    const searchModule = this.modules.filter((module) => module.getType() === searchModuleType)[0];
    if (!searchModule) {
      throw new Error(`Search module ${searchModuleType} not found`);
    }
    return searchModule;
  }

  public async search(query: string) {
    return this.getSearchModule().search(query);
  }

  public async renderResults(target: HTMLElement, results: unknown) {
    this.getSearchModule().renderResults(target, results);
  }

  public getSearchBox() {
    return this.searchBox;
  }
}
