export function sendToLogin() {
  const base = globalThis.NO_ADMIN_BASE ?? "";
  window.location.replace(`${base}/index.php`);
}

export async function fetcher(url, data = {}, method = "POST") {
  const config = {
    method,
    credentials: "include",
    headers: {},
  };

  if (data instanceof FormData) {
    config.body = data;
  } else {
    config.headers["Content-Type"] = "application/json";
    config.body = JSON.stringify(data);
  }

  const res = await fetch(url, config);
  if (res.status === 401) {
    sendToLogin();
    return new Promise(() => {});
  }

  let json;
  try {
    json = await res.json();
  } catch (e) {
    throw new Error("요청 실패");
  }

  if (!res.ok || json.success === false) {
    throw new Error(json.message || json.msg || "요청 실패");
  }

  return json;
}
