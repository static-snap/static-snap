import TextField, { TextFieldProps } from './text-field';

export default function HtmlEditor(props: TextFieldProps) {
  return (
    <TextField
      {...props}
      multiline={true}
      rows={10}
      InputLabelProps={{
        shrink: true,
      }}
    />
  );
}
