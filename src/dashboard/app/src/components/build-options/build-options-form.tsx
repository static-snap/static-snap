import { useCallback, useEffect, useMemo, useState } from 'react';

import LoadingButton from '@mui/lab/LoadingButton';
import Alert from '@mui/material/Alert';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import CardHeader from '@mui/material/CardHeader';
import Chip from '@mui/material/Chip';
import Divider from '@mui/material/Divider';
import FormGroup from '@mui/material/FormGroup';
import FormLabel from '@mui/material/FormLabel';
import Stack from '@mui/material/Stack';
import Typography from '@mui/material/Typography';
import FormProvider from '@staticsnap/dashboard/components/form/form-provider';
import Switch from '@staticsnap/dashboard/components/form/switch';
import useOptions from '@staticsnap/dashboard/hooks/use-options';
import BuildOptionsInterface from '@staticsnap/dashboard/interfaces/build-options.interface';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { useForm } from 'react-hook-form';

type BuildOptionsFormProps = {
  title: string;
};

const BuildOptionsForm = ({ title }: BuildOptionsFormProps) => {
  const [isLoading, setIsLoading] = useState(false);
  const buildOptions = useOptions<BuildOptionsInterface>('build_options');

  const [availablePostTypes, setAvailablePostTypes] = useState<Record<string, string>>({});
  const [updatedPostTypes, setUpdatedPostTypes] = useState<number>(0);

  const defaultValues = useMemo(() => {
    return {
      enable_attachment_pages: false,
      enable_author_pages: false,
      enable_dates_pages: false,
      enable_feed: true,

      enable_rss_feed_atom: false,
      enable_rss_feed_author: false,
      enable_rss_feed_post_comment: false,
      enable_rss_feed_rdf: false,
      enable_rss_feed_rss: true,
      enable_rss_feed_taxonomy: false,

      enable_shortlinks: false,

      enable_terms_pages: false,

      enable_url_finder: true,
    };
  }, []);

  const methods = useForm({
    defaultValues,
  });

  const isDirty = methods.formState.isDirty;

  const enableRssFeed = methods.watch('enable_feed');

  const onSubmit = useCallback(
    async (_data: BuildOptionsInterface) => {
      setIsLoading(true);

      await buildOptions.setOptions(_data as unknown as Record<string, unknown>);
      setUpdatedPostTypes(updatedPostTypes + 1);
      setIsLoading(false);
      methods.reset(methods.getValues());
    },
    [methods, buildOptions, updatedPostTypes]
  );

  const getBuildOptions = useCallback(async () => {
    const options = await buildOptions.getOptions();
    if (options) {
      methods.reset(options);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [methods]);

  const loadDefaultValues = useCallback(() => {
    Object.keys(defaultValues).forEach((key) => {
      methods.setValue(
        key as keyof BuildOptionsInterface,
        defaultValues[key as keyof BuildOptionsInterface],
        { shouldDirty: true }
      );
      methods.trigger(key as keyof BuildOptionsInterface);
    });
  }, [methods, defaultValues]);

  useEffect(() => {
    if (!buildOptions.currentOptions) {
      getBuildOptions();
    }
  }, [buildOptions.currentOptions, getBuildOptions]);

  useEffect(() => {
    const fetchPostTypes = async () => {
      const response = await apiFetch({
        path: '/static-snap/v1/build-options/post-types',
      });
      setAvailablePostTypes(response as Record<string, string>);
    };
    fetchPostTypes();
  }, [updatedPostTypes]);

  return (
    <Card elevation={0}>
      <CardHeader
        title={title}
        action={
          <Button variant="contained" color="info" size="small" onClick={loadDefaultValues}>
            {__('Load default values', 'static-snap')}
          </Button>
        }
      />
      <CardContent>
        {/* Form */}
        <FormProvider methods={methods} onSubmit={methods.handleSubmit(onSubmit)}>
          <Stack direction="column" spacing={2}>
            <FormLabel component="legend">{__('Post types', 'static-snap')}</FormLabel>
            <Stack direction="row" spacing={1} alignItems={'center'}>
              <Typography variant="body2">{__('Enabled post types:', 'static-snap')}</Typography>
              {Object.keys(availablePostTypes).map((postType) => (
                <Chip label={postType} />
              ))}
            </Stack>

            <FormGroup>
              <Switch
                name="enable_attachment_pages"
                label={__('Enable attachment pages', 'static-snap')}
                helperText={__('Enable attachment pages to be built.', 'static-snap')}
              />
            </FormGroup>
            <Divider />
            <FormLabel component="legend">{__('Url finder', 'static-snap')}</FormLabel>
            <FormGroup>
              <Switch
                name="enable_url_finder"
                label={__('Enable url finder', 'static-snap')}
                helperText={__(
                  'Enable url finder to be built. This will find all urls in the content and all the urls will be added to the build queue.',
                  'static-snap'
                )}
              />

              <Alert severity="info">
                {__(
                  'If you enable the URL finder, it will discover URLs to archive pages. Even if archive pages are disabled, those URLs will still be added to the build queue.',
                  'static-snap'
                )}
                <br />

                {__(
                  ' It is recommended to keep the URL finder active to avoid broken links.',
                  'static-snap'
                )}
              </Alert>
            </FormGroup>

            <Divider />
            <FormLabel component="legend">{__('Shortlinks', 'static-snap')}</FormLabel>
            <FormGroup>
              <Switch
                name="enable_shortlinks"
                label={__('Enable shortlinks', 'static-snap')}
                helperText={__(
                  'Enable shortlinks to be built. Nice shortlinks for your posts and pages.',
                  'static-snap'
                )}
              />
              <Alert severity="warning">
                {__(
                  'Experimental feature. This will create shortlinks for your posts and pages. Ex: /pt1s/',
                  'static-snap'
                )}
              </Alert>
            </FormGroup>

            <Divider />
            <FormLabel component="legend">{__('Web feeds', 'static-snap')}</FormLabel>
            <FormGroup>
              <Switch
                name="enable_feed"
                label={__('Enable web feeds', 'static-snap')}
                helperText={__('Enable web feeds to be built.', 'static-snap')}
              />
              {enableRssFeed && (
                <Box sx={{ paddingLeft: 2 }}>
                  <Typography variant={'subtitle2'} mt={2}>
                    {__('Types', 'static-snap')}
                  </Typography>
                  <Switch
                    name="enable_rss_feed_rss"
                    label={__('Enable RSS feed', 'static-snap')}
                    helperText={__('Enable RSS feed to be built.', 'static-snap')}
                  />
                  <Switch
                    name="enable_rss_feed_atom"
                    label={__('Enable Atom feed', 'static-snap')}
                    helperText={__('Enable Atom feed to be built.', 'static-snap')}
                  />

                  <Switch
                    name="enable_rss_feed_rdf"
                    label={__('Enable RDF feed', 'static-snap')}
                    helperText={__('Enable RDF feed to be built.', 'static-snap')}
                  />

                  <Typography variant={'subtitle2'} mt={2}>
                    {__('Additional feeds', 'static-snap')}
                  </Typography>

                  <Switch
                    name="enable_rss_feed_taxonomy"
                    label={__('Enable taxonomy feed', 'static-snap')}
                    helperText={__('Enable taxonomy feed to be built.', 'static-snap')}
                  />
                  <Switch
                    name="enable_rss_feed_author"
                    label={__('Enable author feed', 'static-snap')}
                    helperText={__('Enable author feed to be built.', 'static-snap')}
                  />
                  <Switch
                    name="enable_rss_feed_post_comment"
                    label={__('Enable post comment feed', 'static-snap')}
                    helperText={__('Enable post comment feed to be built.', 'static-snap')}
                  />
                </Box>
              )}
            </FormGroup>
            <Divider />
            <FormLabel component="legend">{__('Archives', 'static-snap')}</FormLabel>
            <FormGroup>
              <Switch
                name="enable_author_pages"
                label={__('Enable author pages', 'static-snap')}
                helperText={__(
                  'Enable author pages to be built. Ex: /author/john-doe/',
                  'static-snap'
                )}
              />
              <Switch
                name="enable_dates_pages"
                label={__('Enable dates pages', 'static-snap')}
                helperText={__('Enable dates pages to be built. Ex: /2021/10/', 'static-snap')}
              />
              <Switch
                name="enable_terms_pages"
                label={__('Enable terms pages', 'static-snap')}
                helperText={__(
                  'Enable terms pages to be built. Ex: /category/news/',
                  'static-snap'
                )}
              />
            </FormGroup>

            {/* Save */}
            <Stack direction="row" sx={{ mt: 3 }}>
              <LoadingButton
                type="submit"
                variant="contained"
                loading={isLoading}
                disabled={!isDirty}
              >
                {__('Save', 'static-snap')}
              </LoadingButton>
            </Stack>
          </Stack>
        </FormProvider>
      </CardContent>
    </Card>
  );
};

export default BuildOptionsForm;
