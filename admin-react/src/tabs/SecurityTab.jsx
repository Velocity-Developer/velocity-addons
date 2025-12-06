import React from 'react';
import FieldCheckbox from '../components/FieldCheckbox';
import FieldInput from '../components/FieldInput';

const SecurityTab = ({ values, onChange }) => (
  <div className="vd-grid">
    <FieldCheckbox
      label="Nonaktifkan XML-RPC"
      checked={values.disable_xmlrpc}
      onChange={(val) => onChange('disable_xmlrpc', val)}
    />
    <FieldCheckbox
      label="Paksa HTTPS"
      checked={values.force_https}
      onChange={(val) => onChange('force_https', val)}
    />
    <FieldInput
      label="Whitelist IP (comma separated)"
      value={values.whitelist_ip}
      onChange={(val) => onChange('whitelist_ip', val)}
      placeholder="1.1.1.1, 8.8.8.8"
    />
  </div>
);

export default SecurityTab;
