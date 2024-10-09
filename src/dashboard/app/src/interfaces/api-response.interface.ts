import ErrorCauseInterface from './error-cause.interface';

// Definición de tipos de API
export interface PaginationInterface {
  total_items: number;
  total_pages: number;
  current_page: number;
  page_size: number;
  next_page?: string;
}

export interface ApiErrorInterface extends Partial<ErrorCauseInterface> {
  code: number;
  message: string;
}

export interface ApiResponseBaseInterface {
  status: 'success' | 'error';
  type: 'item' | 'items' | 'paginated_items' | 'error';
  message?: string;
}

export interface ErrorResponseInterface extends ApiResponseBaseInterface {
  type: 'error';
  error: ApiErrorInterface;
}

export interface ItemResponseInterface<T> extends ApiResponseBaseInterface {
  type: 'item';
  data: T;
}

export interface ItemsResponseInterface<T> extends ApiResponseBaseInterface {
  type: 'items';
  data: T[];
}

export interface PaginatedItemsResponseInterface<T> extends ApiResponseBaseInterface {
  type: 'paginated_items';
  data: T[];
  metadata: {
    pagination: PaginationInterface;
  };
}

type ApiResponseInterface<T> =
  | ErrorResponseInterface
  | ItemResponseInterface<T>
  | ItemsResponseInterface<T>
  | PaginatedItemsResponseInterface<T>;

export type { ApiResponseInterface as default };
