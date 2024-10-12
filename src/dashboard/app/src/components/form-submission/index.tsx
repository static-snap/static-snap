import { useCallback, useState } from 'react';

import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import CardHeader from '@mui/material/CardHeader';
import Divider from '@mui/material/Divider';
import IconButton from '@mui/material/IconButton';
import Snackbar from '@mui/material/Snackbar';
import Stack from '@mui/material/Stack';
import FormSubmissionInterface from '@staticsnap/dashboard/interfaces/form-submission.interface';
import { JsonViewer } from '@textea/json-viewer';
import { __ } from '@wordpress/i18n';

import Icon from '../icon';
import LightTooltip from '../light-tooltip';

interface FormSubmissionProps {
  item: FormSubmissionInterface;
  closeDrawer: () => void;
}

const FormSubmissionRenderableInternalFields = {
  form_submission_form_id: 'Form ID',
  form_submission_form_name: 'Form Name',
  form_submission_form_type: 'Form Type',
};

const FormSubmission = ({ item, closeDrawer }: FormSubmissionProps) => {
  const data = JSON.parse(item.form_submission_data);
  const created = new Date(item.form_submission_created).toLocaleString();
  const [showSnakbar, setShowSnackbar] = useState(false);

  const copyToClipboard = useCallback(() => {
    const clipboardItem = {
      ...item,
      form_submission_data: data,
    };
    navigator.clipboard.writeText(JSON.stringify(clipboardItem, null, 2));
    setShowSnackbar(true);
  }, [item, data]);

  return (
    <Card sx={{ pt: 5, minHeight: '100%', minWidth: '30vw' }}>
      <Snackbar
        open={showSnakbar}
        autoHideDuration={6000}
        onClose={() => setShowSnackbar(false)}
        message={__('Copied to clipboard', 'static-snap')}
      />
      <CardHeader
        title={__('Form Submission', 'static-snap')}
        subheader={created}
        action={
          <>
            <IconButton onClick={copyToClipboard} title={__('Copy to clipboard', 'static-snap')}>
              <Icon icon="material-symbols:content-copy-outline-rounded" />
            </IconButton>
            <IconButton onClick={closeDrawer}>
              <Icon icon="material-symbols:close" />
            </IconButton>
          </>
        }
      />
      <CardContent>
        <Stack spacing={1.5} sx={{ typography: 'body2' }}>
          {Object.keys(FormSubmissionRenderableInternalFields).map((key) => (
            <div key={key}>
              <Stack direction="column" alignItems="flex-start">
                <Box
                  component="span"
                  sx={{ color: 'text.secondary', width: 120, flexShrink: 0 }}
                  key={key}
                >
                  <strong>
                    {
                      FormSubmissionRenderableInternalFields[
                        key as keyof typeof FormSubmissionRenderableInternalFields
                      ]
                    }
                  </strong>
                  :{' '}
                </Box>
                {item[key]}
              </Stack>
            </div>
          ))}
        </Stack>
        <Divider sx={{ my: 2 }} />

        <Stack spacing={1.5} sx={{ typography: 'body2' }}>
          {Object.keys(data).map((key) => (
            <div key={key}>
              <Stack direction="column" alignItems="flex-start">
                <Box
                  component="span"
                  sx={{ color: 'text.secondary', width: 120, flexShrink: 0 }}
                  key={key}
                >
                  <strong>{key}</strong>:{' '}
                </Box>

                {typeof data[key] === 'object' ? (
                  <LightTooltip
                    title={<JsonViewer value={data[key]} theme={'light'} rootName={false} />}
                  >
                    <Button
                      endIcon={<Icon icon={'material-symbols:info'} />}
                      size="small"
                      variant="contained"
                      color="info"
                    >
                      {__('View', 'static-snap')}
                    </Button>
                  </LightTooltip>
                ) : (
                  data[key]
                )}
              </Stack>
            </div>
          ))}
        </Stack>
      </CardContent>
    </Card>
  );
};

export default FormSubmission;
