import React from 'react';

import { Editor, EditorProps } from '@monaco-editor/react';
import { InputBaseComponentProps } from '@mui/material/InputBase';

import TextField, { TextFieldProps } from './text-field';

const EditorInternal = React.forwardRef((props: InputBaseComponentProps) => {
  return (
    <Editor
      defaultLanguage="html"
      width={'100%'}
      height={'20vh'}
      {...(props as EditorProps)}
      options={{
        minimap: {
          enabled: false,
        },
      }}
    />
  );
});

export default function HtmlEditor(props: TextFieldProps) {
  return (
    <TextField
      {...props}
      multiline={true}
      rows={10}
      InputProps={{
        inputComponent: EditorInternal,
      }}
      InputLabelProps={{
        shrink: true,
      }}
    />
  );
}
