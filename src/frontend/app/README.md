# @staticsnap/frontend

`@staticsnap/frontend` is a modular library designed to enhance Static Snap frontend development.

Currently, the frontend package only includes the SearchModule, which allows for the extension of the Search functionality.

## Table of Contents

- [Installation](#installation)
- [Search API](#search-api)
- [Usage](#usage)

## Installation

To install the latest version of `@staticsnap/frontend`, run the following command:

```bash
yarn add @staticsnap/frontend
```


## Search API

### Search Class

The `Search` class is responsible for managing search modules and conducting searches within your application.

#### constructor(frontend: Frontend)
- **Parameters**
  - `frontend`: An instance of `Frontend` used to configure and manage the search settings.
- **Description**
  - Initializes the `Search` class with a `Frontend` instance to handle search configuration.

#### addModule(module: SearchModule)
- **Parameters**
  - `module`: An instance of a class that implements the `SearchModule` interface.
- **Description**
  - Adds a search module to the search system. Multiple modules can be added to handle different types of searches.

#### search(query: string): Promise<any>
- **Parameters**
  - `query`: The search query as a string.
- **Returns**
  - A `Promise` that resolves to the search results.
- **Description**
  - Performs a search using the configured search modules and returns the results asynchronously.

#### renderResults(target: HTMLElement, results: any): void
- **Parameters**
  - `target`: The HTML element where search results should be displayed.
  - `results`: The results of the search query to be rendered.
- **Description**
  - Renders the search results in the specified HTML element.

### SearchModule Interface

The `SearchModule` interface defines the necessary methods for a search module to be integrated into the `Search` class system.

#### getType(): string
- **Returns**
  - A string identifier for the type of search module.
- **Description**
  - Returns the type of the search module, used by `Search` to determine which module to use for a given query.

#### renderResults(target: HTMLElement, results: any): void
- **Parameters**
  - `target`: The HTML element where search results should be displayed.
  - `results`: The results of the search query to be rendered.
- **Description**
  - Renders the search results in the specified HTML element. Implementation can vary based on the type of search results and frontend requirements.

#### search(query: string): Promise<any>
- **Parameters**
  - `query`: The search query as a string.
- **Returns**
  - A `Promise` that resolves to the search results.
- **Description**
  - Executes a search based on the provided query and returns a promise with the results. Implementations may vary depending on the search algorithm and data source.


## Usage

```typescript
import { SearchModule } from '@staticsnap/frontend';
class MySearchModule implements SearchModule {
  public getType(): string {
    return 'my-search-module';
  }
  public async search(query: string): Promise<string[]> {
    console.log('My search', query);
    return ['search'];
  }
  public renderResults(target: HTMLElement, results: unknown): void {
    console.log('My render results', target, results);
  }
}

export default MySearchModule;

// add it to the frontend
window.StaticSnapFrontend.Search.addModule(new MySearchModule());
```
