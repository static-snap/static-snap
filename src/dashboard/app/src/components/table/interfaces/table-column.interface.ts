export default interface TableColumnInterface {
  field: string;
  headerName: string;
  width?: number; // Optional, in case you want to specify column widths
  render?: (value: any, column: TableColumnInterface, row: any) => any; // Optional, in case you want to render the cell differently
}
