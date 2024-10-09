/**
 * Interface for search modules
 */
export default interface SearchModule {
  /**
   * Get the type of the search module
   * @example
   * ```typescript
   * public getType() {
   *  return 'fuse-js';
   * }
   * ```
   */
  getType: () => string;

  /**
   * Render the search results
   * @param target  - The target element to render the results
   * @param results - The search results
   * @example
   * ```typescript
   * renderResults(target: HTMLElement, results: any) {
   * target.innerHTML = results.map((result) => `<li>${result.title}</li>`).join('');
   * }
   * ```
   */
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  renderResults: (target: HTMLElement, results: any) => void;
  /**
   * Search the query
   * @param query - The search query
   * @example
   * ```typescript
   * search(query: string): Promise<string[]> {
   * const myIndex = ["a","b","c"];
   * return myIndex.filter((item) => item.includes(query));
   * }
   * ```
   */
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  search: (query: string) => Promise<any>;
}
