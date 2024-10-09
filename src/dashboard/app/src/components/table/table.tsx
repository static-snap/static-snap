import CardContent from '@mui/material/CardContent';
import CardHeader from '@mui/material/CardHeader';
import Card from '@mui/material/Card';
import MuiTable from '@mui/material/Table';
import MuiTableContainer from '@mui/material/TableContainer';

import { ButtonRowProps } from './interfaces/button-row-props';
import TableColumnInterface from './interfaces/table-column.interface';
import TableRowInterface from './interfaces/table-row.interface';
import TableContents from './table-contents';
import TableSkeleton from './table-skeleton';

type TableContainerProps = {
  rows: TableRowInterface[];
  columns: TableColumnInterface[];
  actions?: ButtonRowProps[];
  loading: boolean;
};

type TableProps = {
  title: string;
  subTitle?: string;

  cardActions?: React.ReactElement;
};

export function TableContainer({
  rows,
  columns,
  actions = [],
  loading = false,
}: TableContainerProps) {
  return (
    <MuiTableContainer>
      <MuiTable aria-label="Table">
        {loading ? (
          <TableSkeleton columns={columns} />
        ) : (
          <TableContents columns={columns} rows={rows} actions={actions} />
        )}
      </MuiTable>
    </MuiTableContainer>
  );
}
export default function Table({
  title,
  subTitle,
  rows,
  columns,
  actions = [],
  cardActions,
  loading = false,
}: TableProps & TableContainerProps) {
  return (
    <Card elevation={0}>
      <CardHeader title={title} subheader={subTitle} action={cardActions} />
      <CardContent>
        <TableContainer rows={rows} columns={columns} actions={actions} loading={loading} />
      </CardContent>
    </Card>
  );
}
