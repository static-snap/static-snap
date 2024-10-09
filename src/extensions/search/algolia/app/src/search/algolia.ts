import { MultipleQueriesResponse } from '@algolia/client-search';
import { Frontend, SearchModuleInterface } from '@staticsnap/frontend';
import { __ } from '@wordpress/i18n';
import initAlgolia, { SearchClient } from 'algoliasearch';
import { SearchResponse } from 'instantsearch.js';
import Mustache from 'mustache';

type AlgoliaSettings = {
  algolia_application_id: string;
  algolia_search_key: string;
  algolia_index_name: string;
};
class Algolia implements SearchModuleInterface {
  private client: SearchClient;
  private frontend: Frontend;
  private settings: AlgoliaSettings;

  constructor(frontend: Frontend) {
    this.frontend = frontend;
    this.settings = this.frontend.config().options.search.settings as AlgoliaSettings;
    this.client = initAlgolia(
      this.settings.algolia_application_id,
      this.settings.algolia_search_key
    );
  }
  public getType(): string {
    return 'algolia';
  }
  public async search(query: string): Promise<MultipleQueriesResponse<DocumentType>> {
    const locale = this.frontend.config().locale || null;
    const has_translations = this.frontend.config().has_translations || false;
    const params: Record<string, string[] | string> = {};
    if (has_translations) {
      params.facetFilters = [`language:${locale}`];
    }

    const multipleQueries = [
      {
        indexName: this.settings.algolia_index_name,
        params: params,
        query,
      },
    ];

    try {
      return await this.client.search(multipleQueries);
    } catch (error: unknown) {
      if (this.frontend.config().is_admin_bar_showing) {
        const message = (error as Error).message;
        alert(
          __(
            `You must run a deployment to index the content for first time when you are using wordpress. Error: ${message}`
          )
        );
      }
      return { results: [] };
    }
  }
  public renderResults(target: HTMLElement, results: MultipleQueriesResponse<DocumentType>): void {
    if (!results.results) {
      return;
    }
    const template = document.getElementById('static-snap-search-result-template')?.innerHTML;

    if (!template) {
      return;
    }

    const rendered = Mustache;
    let html = '';
    for (const result of results.results) {
      const hits = (result as SearchResponse<DocumentType>).hits;
      const itemHtml = hits
        .map((hit) => {
          return rendered.render(template, hit);
        })
        .join('');
      html += '<li>' + itemHtml + '</li>';
    }

    target.innerHTML = '<ol>' + html + '</ol>';
  }
}

export default Algolia;
