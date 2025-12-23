import { graphQL_Query } from "./graphql";
import { USER_FRAGMENT } from "./fragments";
import Cookies from "js-cookie";
import { Item } from "@/classes/ItemClass";
import { ApolloError } from "@apollo/client";
import { loginFormState } from "@/store/store";
import { GET_PRODCUTS_BY_ID } from "./fragments";
import type { ProductType } from "@/utils/Types";

function setWrongLogin(value: boolean) {
  loginFormState.setState({ isError: value });
}
function normalizeProductsPayload(payload: unknown): ProductType[] {
  if (!payload || typeof payload !== "object") return [];

  const root = payload as any;

  const products =
    Array.isArray(root.products) ? root.products :
    Array.isArray(root.data?.products) ? root.data.products :
    Array.isArray(root.data) ? root.data :
    [];

  return products.map((item: ProductType) => ({
    ...item,
    image: item?.image ?? null,
    category: item?.category ?? { title: "" },
  }));
}

export async function login(identifier: string, password: string) {
  try {
    const baseUrl = process.env.NEXT_PUBLIC_API_BASE_URL;
    if (!baseUrl) {
      throw new Error("API base URL is not configured");
    }

    const response = await fetch(`${baseUrl}/api/login`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        identifier,
        password,
      }),
    });

    if (!response.ok) {
      setWrongLogin(true);
      return;
    }

    const data = await response.json();

    if (!data?.success || !data.jwt || !data.documentId || !data.user?.id) {
      setWrongLogin(true);
      return;
    }

    // Store session data in cookies for later use across the app
    Cookies.set("jwt", data.jwt, { path: "/", sameSite: "Lax" });
    Cookies.set("document", String(data.documentId), {
      path: "/",
      sameSite: "Lax",
    });
    Cookies.set("user", String(data.user.id), { path: "/", sameSite: "Lax" });

    if (data.user.first_name) {
      Cookies.set("first_name", String(data.user.first_name), {
        path: "/",
        sameSite: "Lax",
      });
    }

    if (data.user.last_name) {
      Cookies.set("last_name", String(data.user.last_name), {
        path: "/",
        sameSite: "Lax",
      });
    }

    if (data.code) {
      Cookies.set("code", String(data.code), { path: "/", sameSite: "Lax" });
    }

    if (data.company) {
      Cookies.set("company", String(data.company), {
        path: "/",
        sameSite: "Lax",
      });
    }

    setWrongLogin(false);
    window.location.href = "/";
  } catch (error: unknown) {
    if (error instanceof Error) {
      console.error("JS error:", error.message);
    } else {
      console.error("Unknown error:", error);
    }
    setWrongLogin(true);
  }
}

export async function get_current_user(id: string) {
  try {
    const response = await graphQL_Query(USER_FRAGMENT, { id });
    if (!response || response.errors) {
      throw new Error(
        response?.errors?.[0]?.message || "Login failed due to an unknown error"
      );
    }
    return response;
  } catch (error: unknown) {
    if (error instanceof ApolloError) {
      console.error("GraphQL error:", error.message);
    } else if (error instanceof Error) {
      console.error("JS error:", error.message);
    } else {
      console.error("Unknown error:", error);
    }
  }
}

export async function change_password_query(
  old_password: string,
  new_password: string,
  confirm_password: string
) {
  try {
    const baseUrl = process.env.NEXT_PUBLIC_API_BASE_URL;
    if (!baseUrl) {
      throw new Error("API base URL is not configured");
    }

    const jwt = Cookies.get("jwt");
    if (!jwt) {
      throw new Error("Not authenticated");
    }

    const response = await fetch(`${baseUrl}/api/change-password`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        Authorization: `Bearer ${jwt}`,
      },
      body: JSON.stringify({
        old_password,
        new_password,
        confirm_password,
      }),
    });

    if (!response.ok) {
      const errorBody = await response.json().catch(() => null);
      const msg =
        (errorBody && (errorBody.message || errorBody.error)) ||
        "Password change failed";
      throw new Error(msg);
    }

    return await response.json();
  } catch (error: unknown) {
    if (error instanceof Error) {
      console.error("JS error:", error.message);
    } else {
      console.error("Unknown error:", error);
    }
  }
}

export async function get_products(start: number, limit: number, cat?: string) {
  try {
    const baseUrl = process.env.NEXT_PUBLIC_API_BASE_URL;
    if (!baseUrl) throw new Error("API base URL is not configured");

    const params = new URLSearchParams({
      start: String(start),
      limit: String(limit),
    });
    if (cat) params.append("cat", cat);

    const res = await fetch(`${baseUrl}/api/products?${params.toString()}`, {
      method: "GET",
      headers: { Accept: "application/json" },
      cache: "no-store", // avoids “stale 1 item” weirdness in Next
    });

    if (!res.ok) {
      console.error("Error fetching products:", res.status, res.statusText);
      return { products: [] as ProductType[] };
    }

    const json = await res.json();
    const products = normalizeProductsPayload(json);

    if (process.env.NODE_ENV !== "production") {
      const keys = json && typeof json === "object" ? Object.keys(json) : [];
      console.debug("[get_products] response keys:", keys);
      console.debug("[get_products] normalized length:", products.length);
    }

    return { products };
  } catch (error) {
    console.error("Error fetching products:", error);
    return { products: [] as ProductType[] };
  }
}

export async function add_order(
  items: Item[],
  clientCode: string,
  total: number,
  vetgroupUserId: string
) {
  try {
    const baseUrl = process.env.NEXT_PUBLIC_API_BASE_URL;
    if (!baseUrl) {
      throw new Error("API base URL is not configured");
    }

    const payload = {
      total,
      vetgroup_user_id: Number(vetgroupUserId),
      client_code: clientCode,
      products: items.map((item) => ({
        name: item.name,
        description: item.description,
        qty: item.qty,
        price: item.price,
      })),
      items_list: items.map((item) => ({
        ItemID: item.backendId ?? "",
        Quantity: item.qty ?? 0,
      })),
    };

    const response = await fetch(`${baseUrl}/api/orders`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      const error = await response.text();
      console.error("Failed to create order:", error);
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const result = await response.json();
    return result;
  } catch (error: unknown) {
    if (error instanceof Error) {
      console.error("JS error:", error.message);
    } else {
      console.error("Unknown error:", error);
    }
    return null;
  }
}

export async function get_user_orders(userId: string) {
  try {
    const baseUrl = process.env.NEXT_PUBLIC_API_BASE_URL;
    if (!baseUrl) {
      throw new Error("API base URL is not configured");
    }

    const params = new URLSearchParams({
      userId,
    });

    const response = await fetch(`${baseUrl}/api/orders?${params.toString()}`, {
      method: "GET",
      headers: {
        Accept: "application/json",
      },
    });

    if (!response.ok) {
      throw new Error("Invalid response from API");
    }

    const data = await response.json();

    if (!data || !data.orders) {
      throw new Error("Invalid response from API");
    }

    return data;
  } catch (error: unknown) {
    if (error instanceof Error) {
      console.error("JS error:", error.message);
    } else {
      console.error("Unknown error:", error);
    }
  }
}

export async function get_order_details(orderId: number | string) {
  try {
    const baseUrl = process.env.NEXT_PUBLIC_API_BASE_URL;
    if (!baseUrl) {
      throw new Error("API base URL is not configured");
    }

    const response = await fetch(`${baseUrl}/api/orders/${orderId}`, {
      method: "GET",
      headers: {
        Accept: "application/json",
      },
    });

    if (!response.ok) {
      throw new Error("Invalid response from API");
    }

    const data = await response.json();

    if (!data) {
      throw new Error("Invalid response from API");
    }

    // Prefer `products` array, but allow older `products_json` shape too.
    if (Array.isArray(data.products)) {
      return { products: data.products };
    }

    if (Array.isArray(data.products_json)) {
      return { products: data.products_json };
    }

    throw new Error("Invalid response from API");
  } catch (error: unknown) {
    if (error instanceof Error) {
      console.error("JS error:", error.message);
    } else {
      console.error("Unknown error:", error);
    }
  }
}

export async function get_categories() {
  try {
    const baseUrl = process.env.NEXT_PUBLIC_API_BASE_URL;
    if (!baseUrl) {
      throw new Error("API base URL is not configured");
    }

    const response = await fetch(`${baseUrl}/api/categories`, {
      method: "GET",
      headers: {
        Accept: "application/json",
      },
    });

    if (!response.ok) {
      console.error("Error fetching categories:", response.statusText);
      return { categories: [] };
    }

    const data = await response.json();
    return data;
  } catch (error: unknown) {
    if (error instanceof Error) {
      console.error("JS error:", error.message);
    } else {
      console.error("Unknown error:", error);
    }
    return { categories: [] };
  }
}

export async function search_products(query: string, cat?: string) {
  try {
    const baseUrl = process.env.NEXT_PUBLIC_API_BASE_URL;
    if (!baseUrl) {
      throw new Error("API base URL is not configured");
    }

    const params = new URLSearchParams({
      start: "0",
      limit: "500",
      search: query,
    });

    if (cat) {
      params.append("cat", cat);
    }

    const response = await fetch(`${baseUrl}/api/products?${params.toString()}`, {
      method: "GET",
      headers: {
        Accept: "application/json",
      },
    });

    if (!response.ok) {
      console.error("Error searching products:", response.statusText);
      return { products: [] };
    }

    const data = await response.json();
    return data || { products: [] };
  } catch (error: unknown) {
    if (error instanceof Error) {
      console.error("JS error:", error.message);
    } else {
      console.error("Unknown error:", error);
    }
    return { products: [] };
  }
}
// eslint-disable-next-line @typescript-eslint/no-unused-vars
export async function add_strapi_order(..._args: unknown[]) {
  // Kept for backward compatibility; order creation is now handled
  // by the Laravel REST API via add_order().
  return { status: true };
}

export async function updateProductStock(code: string, qty: number) {
  try {
    // Step 1: Get product by code
    const getRes = await fetch(
      `https://vetgroup.am/api/products?filters[code][$eq]=${encodeURIComponent(
        code
      )}`
    );
    const getData = await getRes.json();
    const product = getData?.data?.[0];

    if (!product) {
      throw new Error(`Product with code ${code} not found`);
    }

    const productId = product.id;
    const currentStock = product.stock ?? 0;
    const newStock = qty === 0 ? 0 : Math.max(currentStock - qty, 0);

    // Step 2: Update stock
    const putRes = await fetch(
      `https://vetgroup.am/api/vetgroup-product/${productId}`,
      {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ data: { stock: newStock } }),
      }
    );

    const result = await putRes.json();

    if (result.error) throw new Error(result.error.message);

    return true;
  } catch (err) {
    console.error("❌ Stock update failed for", code, err);
    return false;
  }
}


export async function get_product_by_id(id: string) {
  try {
    const response = await graphQL_Query(GET_PRODCUTS_BY_ID, { id: id });
    return response;
  } catch (error: unknown) {
    if (error instanceof ApolloError) {
      console.error("GraphQL error:", error.message);
    } else if (error instanceof Error) {
      console.error("JS error:", error.message);
    } else {
      console.error("Unknown error:", error);
    }
  }
}
