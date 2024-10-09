import Fuse, { FuseResult, FuseIndex, Expression } from 'fuse.js';
import Mustache from 'mustache';

import Frontend from '../frontend';
import DocumentType from '../interfaces/document.interface';
import SearchModule from '../interfaces/search-module.interface';

export default class FuseSearch implements SearchModule {
  private frontend: Frontend;
  constructor(frontend: Frontend) {
    this.frontend = frontend;
  }

  public getType() {
    return 'fuse-js';
  }

  private fuse: Fuse<DocumentType> | null = null;

  private searchData: DocumentType[] = [];
  private searchIndex: FuseIndex<DocumentType> | null = null;

  private fuse_options = {
    distance: 100,
    // includeMatches: false,
    findAllMatches: true,
    // isCaseSensitive: false,

    ignoreFieldNorm: false,
    // this is the easiest way to get the search to work if you don't know about scoring theory
    // read more at here: https://www.fusejs.io/concepts/scoring-theory.html
    ignoreLocation: true,
    includeScore: true,

    keys: ['title', 'content', 'excerpt', 'language'],
    shouldSort: true,
    // minMatchCharLength: 1,
    // location: 0,
    threshold: 0.55,

    useExtendedSearch: true,
  };

  public async search(query: string): Promise<FuseResult<DocumentType>[]> {
    const locale = this.frontend.config().locale || null;
    const has_translations = this.frontend.config().has_translations || false;
    const fuse = await this.getFuse();
    const extendedQuery: Expression = {
      $and: [{ $or: [{ title: query }, { content: query }, { excerpt: query }] }],
    };
    if (locale && has_translations) {
      extendedQuery.$and?.push({ language: `=${locale}` });
    }

    const results = fuse.search(extendedQuery);
    return results;
  }

  private getOptions() {
    const config = this.frontend.config().options.search?.settings || {};
    // convert all fuse_* to fuse options
    Object.keys(config).forEach((key) => {
      if (key.startsWith('fuse_')) {
        const option = key.replace('fuse_', '') as keyof typeof this.fuse_options;
        const value = config[key as keyof typeof config];
        (this.fuse_options[option] as typeof value) = value;
      }
    });

    return this.fuse_options;
  }

  public async getFuse(): Promise<Fuse<DocumentType>> {
    await this.getSearchIndex();
    this.fuse = new Fuse<DocumentType>(
      this.searchData,
      this.fuse_options,
      this.searchIndex as FuseIndex<DocumentType>
    );
    return this.fuse;
  }

  public async getSearchIndex(force = false) {
    if (this.searchIndex && !force) {
      return this.searchIndex;
    }
    const response = await fetch(this.frontend.config().search_index_url);
    this.searchData = await response.json();

    this.getOptions();
    this.searchIndex = await Fuse.createIndex<DocumentType>(
      this.fuse_options.keys,
      this.searchData
    );

    return this.searchIndex;
  }

  public renderResults(target: HTMLElement, results: FuseResult<DocumentType>[]) {
    const template = document.getElementById('static-snap-search-result-template')?.innerHTML;
    if (!template) {
      return;
    }

    const rendered = Mustache;
    const html = results
      .map((result) => {
        return '<li>' + rendered.render(template, result.item) + '</li>';
      })
      .join('');

    target.innerHTML = '<ol>' + html + '</ol>';
  }
}
