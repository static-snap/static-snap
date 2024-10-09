import MuiTable from '@mui/material/Table';
import TableBody from '@mui/material/TableBody';
import TableCell from '@mui/material/TableCell';
import TableContainer from '@mui/material/TableContainer';
import TableHead from '@mui/material/TableHead';
import TableRow from '@mui/material/TableRow';
import TableColumnInterface from './interfaces/table-column.interface';
import Skeleton from '@mui/material/Skeleton';

type TableProps = {
  columns: TableColumnInterface[];
};
const TableSkeleton = ({ columns }: TableProps) => (
  <TableContainer>
    <MuiTable aria-label="Table">
      <TableHead>
        <TableRow>
          {columns.map((column) => (
            <TableCell key={column.field}>
              <Skeleton variant="rectangular" width={'100%'} height={35} />
            </TableCell>
          ))}
        </TableRow>
      </TableHead>
      <TableBody>
        <TableRow>
          {columns.map((column) => (
            <TableCell key={column.field}>
              <Skeleton variant="rectangular" width={'100%'} height={35} />
            </TableCell>
          ))}
        </TableRow>
      </TableBody>
    </MuiTable>
  </TableContainer>
);

export default TableSkeleton;
