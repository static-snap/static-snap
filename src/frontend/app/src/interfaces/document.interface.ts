/**
 * Document type used in the search module
 */
type DocumentType = {
  title: string;
  content: string;
  excerpt: string;
  url: string;

  [locale: string]: unknown;
};

export default DocumentType;
