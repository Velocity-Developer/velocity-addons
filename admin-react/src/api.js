export const createApi = ({ nonce, restUrl, optionsEndpoint }) => {
  const headers = {
    'Content-Type': 'application/json',
    'X-WP-Nonce': nonce,
  };

  const getOptions = async () => {
    const res = await fetch(`${restUrl}${optionsEndpoint}`, {
      headers,
      credentials: 'same-origin',
    });
    if (!res.ok) throw new Error('Gagal memuat');
    return res.json();
  };

  const saveOptions = async (data) => {
    const res = await fetch(`${restUrl}${optionsEndpoint}`, {
      method: 'POST',
      headers,
      credentials: 'same-origin',
      body: JSON.stringify(data),
    });
    if (!res.ok) throw new Error('Gagal menyimpan');
    return res.json();
  };

  return { getOptions, saveOptions };
};
