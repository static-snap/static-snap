import { useCallback, useEffect, useState } from 'react';

import Box from '@mui/material/Box';
import Drawer from '@mui/material/Drawer';
import IconButton from '@mui/material/IconButton';
import Menu from '@mui/material/Menu';
import MenuItem from '@mui/material/MenuItem';
import Pagination from '@mui/material/Pagination';
import ConnectFallback from '@staticsnap/dashboard/components/connect-fallback';
import FormSubmission from '@staticsnap/dashboard/components/form-submission';
import Icon from '@staticsnap/dashboard/components/icon';
import LightTooltip from '@staticsnap/dashboard/components/light-tooltip';
import Table from '@staticsnap/dashboard/components/table/table';
import useOptions from '@staticsnap/dashboard/hooks/use-options';
import useStaticSnapAPI from '@staticsnap/dashboard/hooks/use-static-snap-api';
import ConnectInterface from '@staticsnap/dashboard/interfaces/connect.interface';
import FormSubmissionInterface from '@staticsnap/dashboard/interfaces/form-submission.interface';
import { JsonViewer } from '@textea/json-viewer';
import { __ } from '@wordpress/i18n';

const FormSumissionsIndex = () => {
  const options = useOptions<ConnectInterface>('connect');
  const staticSnap = useStaticSnapAPI('/forms/submissions', { method: 'GET' });
  const staticSnapDeleteSubmission = useStaticSnapAPI('/forms/submissions/delete', {
    method: 'DELETE',
  });
  const [formSubmissions, setFormSubmissions] = useState<FormSubmissionInterface[]>([]);
  const [pages, setPages] = useState(0);
  const [currentPage, setCurrentPage] = useState(1);

  const [downloadAnchorEl, setDownloadAnchorEl] = useState<null | HTMLElement>(null);
  const [downloadIsLoading, setDownloadIsLoading] = useState(false);
  const downloadOptionsOpen = Boolean(downloadAnchorEl);

  const downloadOptionsHandleClick = useCallback((event: React.MouseEvent<HTMLElement>) => {
    setDownloadAnchorEl(event.currentTarget);
  }, []);
  const downloadOpotionsHandleClose = useCallback(() => {
    setDownloadAnchorEl(null);
  }, []);

  const [drawerOpen, setDrawerOpen] = useState(false);

  const [currentFormSubmissionItem, setCurrentFormSubmissionItem] =
    useState<FormSubmissionInterface | null>(null);

  const closeDrawer = useCallback(() => {
    setDrawerOpen(false);
  }, []);

  const openDrawer = useCallback(() => {
    setDrawerOpen(true);
  }, []);

  const getAllFormSubmissions = useCallback(async (): Promise<FormSubmissionInterface[]> => {
    const effectOptions = await options.getOptions();
    if (!effectOptions?.website_id) {
      throw new Error('Website ID is required');
    }
    let results: FormSubmissionInterface[] = [];
    const response = await staticSnap.request<FormSubmissionInterface>(
      {},
      `/${effectOptions.website_id}?page=${1}&limit=50`
    );

    if (response.type === 'paginated_items') {
      const pages = response.metadata.pagination.total_pages;
      results = response.data;

      for (let i = 2; i <= pages; i++) {
        const response = await staticSnap.request<FormSubmissionInterface>(
          {},
          `/${effectOptions.website_id}?page=${i}&limit=50`
        );
        if (response.type === 'paginated_items') {
          results.push(...response.data);
        }
      }
    }

    return results;
  }, [options, staticSnap]);

  const downloadCSV = useCallback(async () => {
    downloadOpotionsHandleClose();
    if (downloadIsLoading) {
      return;
    }

    setDownloadIsLoading(true);
    try {
      const results = await getAllFormSubmissions();

      const csvHeader = 'id,user_id,website_id,form_id,form_name,form_type,data,created\n';

      const csv = results.map((result) => {
        const encodedJSONData = result.form_submission_data.replace(/"/g, '""');
        return `"${result.form_submission_id}","${result.form_submission_user_id}","${result.form_submission_website_id}","${result.form_submission_form_id}","${result.form_submission_form_name}","${result.form_submission_form_type}","${encodedJSONData}","${result.form_submission_created}"`;
      });

      const csvString = csvHeader + csv.join('\n');
      const blob = new Blob([csvString], { type: 'text/csv' });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'form-submissions.csv';
      a.click();
    } catch (error) {
      console.error(error);
    }
    setDownloadIsLoading(false);
  }, [downloadIsLoading, downloadOpotionsHandleClose, getAllFormSubmissions]);

  const downloadJSON = useCallback(async () => {
    downloadOpotionsHandleClose();
    if (downloadIsLoading) {
      return;
    }
    setDownloadIsLoading(true);
    try {
      const _results = await getAllFormSubmissions();
      const results = _results.map((result) => {
        let JSONData = {};
        try {
          JSONData = JSON.parse(result.form_submission_data);
        } catch (error) {
          console.error(error);
        }
        return {
          ...result,
          form_submission_data: JSONData,
        };
      });
      const blob = new Blob([JSON.stringify(results)], { type: 'application/json' });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'form-submissions.json';
      a.click();
    } catch (error) {
      console.error(error);
    }
    setDownloadIsLoading(false);
  }, [downloadIsLoading, downloadOpotionsHandleClose, getAllFormSubmissions]);

  const getFormSubmissions = useCallback(
    async (page: number = 1) => {
      const effectOptions = await options.getOptions();
      if (!effectOptions?.website_id) {
        return;
      }
      const response = await staticSnap.request<FormSubmissionInterface>(
        {},
        `/${effectOptions.website_id}?page=${page}`
      );

      if (response.type === 'paginated_items') {
        setFormSubmissions(response.data);
        setPages(response.metadata.pagination.total_pages);
      }
    },
    [options, staticSnap]
  );

  const onChangePage = useCallback(
    (_event: React.ChangeEvent<unknown>, page: number) => {
      if (page === currentPage) {
        return;
      }
      setCurrentPage(page);
      getFormSubmissions(page);
      // go to form-submissions-table-anchor smoothly
      document.getElementById('form-submissions-table-anchor')?.scrollIntoView({
        behavior: 'smooth',
      });
    },
    [getFormSubmissions, currentPage]
  );

  useEffect(() => {
    getFormSubmissions();
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  return (
    <ConnectFallback
      connect={options.currentOptions as ConnectInterface}
      isLoading={!options.currentOptionsHasBeenCalled}
    >
      <Drawer anchor="right" open={drawerOpen} onClose={closeDrawer}>
        {currentFormSubmissionItem && (
          <FormSubmission item={currentFormSubmissionItem} closeDrawer={closeDrawer} />
        )}
      </Drawer>
      <div id="form-submissions-table-anchor" />
      <Table
        title="Form Submissions"
        subTitle={__('Forms submitted by your static website')}
        loading={!options.currentOptionsHasBeenCalled}
        cardActions={
          <>
            <IconButton
              onClick={downloadOptionsHandleClick}
              disabled={downloadIsLoading}
              color="primary"
              title={__('Download all submissions', 'static-snap')}
            >
              {downloadIsLoading ? (
                <Icon icon="line-md:downloading-loop" />
              ) : (
                <Icon icon="material-symbols:downloading-rounded" />
              )}
            </IconButton>
            <Menu
              id="long-menu"
              MenuListProps={{
                'aria-labelledby': 'long-button',
              }}
              anchorEl={downloadAnchorEl}
              open={downloadOptionsOpen}
              onClose={downloadOpotionsHandleClose}
            >
              <MenuItem onClick={downloadCSV}>{__('Download CSV', 'static-snap')}</MenuItem>
              <MenuItem onClick={downloadJSON}>{__('Download JSON', 'static-snap')}</MenuItem>
            </Menu>
          </>
        }
        rows={formSubmissions.map((formSubmission) => ({
          id: formSubmission.form_submission_id,
          ...formSubmission,
        }))}
        columns={[
          {
            field: 'form_submission_form_name',
            headerName: 'Name',
            render: (value) => value || '-',
          },
          {
            field: 'form_submission_data',
            headerName: 'Data',
            render: (value) =>
              value ? (
                <LightTooltip
                  title={<JsonViewer value={JSON.parse(value)} theme={'light'} rootName={false} />}
                >
                  <Icon icon={'material-symbols:info'} />
                </LightTooltip>
              ) : null,
          },
          {
            field: 'form_submission_created',
            headerName: 'Created',
            render: (value) => {
              return new Date(value).toLocaleString();
            },
          },
        ]}
        actions={[
          {
            children: 'View',
            color: 'primary',
            id: 'view',
            onRowClick: (_row, index) => {
              setCurrentFormSubmissionItem(formSubmissions[index]);
              openDrawer();
            },
            size: 'small',
            startIcon: <Icon icon="material-symbols:visibility" />,
            variant: 'contained',
          },
          {
            children: 'Delete',
            color: 'error',
            id: 'delete',
            onRowClick: async (row, index) => {
              const confirm = window.confirm(
                __(
                  'Are you sure you want to delete this form submission? This action cannot be undone.',
                  'static-snap'
                )
              );
              if (!confirm) {
                return;
              }
              const response = await staticSnapDeleteSubmission.request<boolean>({
                body: JSON.stringify({
                  form_submission_id: row.form_submission_id,
                }),
              });
              if (response.status === 'success' && response.type === 'item' && response.data) {
                setFormSubmissions((prev) => {
                  const newFormSubmissions = [...prev];
                  newFormSubmissions.splice(index, 1);
                  return newFormSubmissions;
                });
              }
            },
            size: 'small',
            startIcon: <Icon icon="material-symbols:delete" />,
            variant: 'contained',
          },
        ]}
      />
      <Box sx={{ display: 'flex', justifyContent: 'center', mt: 2 }}>
        <Pagination count={pages} color="primary" onChange={onChangePage} page={currentPage} />
      </Box>
    </ConnectFallback>
  );
};

export default FormSumissionsIndex;
