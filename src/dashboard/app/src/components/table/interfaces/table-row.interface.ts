export default interface TableRowInterface {
  id: string | number;
  [key: string]: any; // This allows for any other key-value pairs, making the row data flexible
}
