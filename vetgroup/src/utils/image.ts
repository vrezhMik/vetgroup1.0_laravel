export function buildImageUrl(path?: string | null): string {
  if (!path) {
    return "/placeholder.webp";
  }

  // If it's already an absolute URL, use it as-is
  if (/^https?:\/\//i.test(path)) {
    return path;
  }

  const base = (process.env.NEXT_PUBLIC_API_BASE_URL ?? "").replace(/\/$/, "");
  const cleanedPath = path.replace(/^\//, "");

  if (!base) {
    return `/${cleanedPath}`;
  }

  return `${base}/${cleanedPath}`;
}
