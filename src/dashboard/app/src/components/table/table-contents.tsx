import Button from '@mui/material/Button';
import Stack from '@mui/material/Stack';
import TableBody from '@mui/material/TableBody';
import TableCell from '@mui/material/TableCell';
import TableHead from '@mui/material/TableHead';
import TableRow from '@mui/material/TableRow';

import { ButtonRowProps } from './interfaces/button-row-props';
import TableColumnInterface from './interfaces/table-column.interface';
import TableRowInterface from './interfaces/table-row.interface';

type TableContentsProps = {
  columns: TableColumnInterface[];
  rows: TableRowInterface[];
  actions?: ButtonRowProps[];
};

const uuid = () => Math.floor(Math.random() * 1000000);

const TableContents = ({ columns, rows, actions }: TableContentsProps) => {
  return (
    <>
      <TableHead>
        <TableRow>
          {columns.map((column) => (
            <TableCell key={column.field}>{column.headerName}</TableCell>
          ))}

          {actions && <TableCell></TableCell>}
        </TableRow>
      </TableHead>
      <TableBody>
        {rows.map((row, index) => (
          <TableRow key={row.id || uuid()}>
            {columns.map((column) => (
              <TableCell key={column.field}>
                {typeof column.render === 'function'
                  ? column.render(row[column.field], column, row)
                  : row[column.field]}
              </TableCell>
            ))}
            <TableCell>
              <Stack spacing={1.5} sx={{ typography: 'body2' }} direction="row">
                {actions?.map(({ onRowClick, onClick, shouldRender, ...other }) => {
                  if (shouldRender && !shouldRender(row, index)) {
                    return null;
                  }
                  return (
                    <Button
                      key={other.id}
                      {...other}
                      onClick={(e) => {
                        onRowClick && onRowClick(row, index);
                        onClick && onClick(e);
                      }}
                    />
                  );
                })}
              </Stack>
            </TableCell>
          </TableRow>
        ))}
      </TableBody>
    </>
  );
};

export default TableContents;
