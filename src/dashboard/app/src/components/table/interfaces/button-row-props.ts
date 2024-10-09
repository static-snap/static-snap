import { ButtonProps } from '@mui/material/Button';

import TableRowInterface from './table-row.interface';

export type ButtonRowProps = {
  onRowClick: (row: TableRowInterface, index: number) => void;
  shouldRender?: (row: TableRowInterface, index: number) => boolean;
} & ButtonProps;
