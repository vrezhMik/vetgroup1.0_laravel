"use client";

import { useEffect, useState } from "react";
import { useCart, useCard, useCardView, HistoryCardState } from "@/store/store";
import TrashSVG from "../../Elements/Icons/TrashSVG";
import style from "./cardListView.module.scss";
import { CardView, ProductType } from "@/utils/Types";
import { buildImageUrl } from "@/utils/image";
import { getCookie } from "@/utils/cookies";
import { add_order } from "@/utils/query";
import { Item } from "@/classes/ItemClass";
import Cookies from "js-cookie";
import ArrowSVG from "@/components/Elements/Icons/ArrowSVG";
import ImageComponent from "@/components/Elements/Image/image.component";

/** ===== Types ===== */

type OrderProductType = {
  ItemsList: Array<{
    ItemID: string;
    Quantity: number;
  }>;
};

type VisibleItems = ProductType[] | OrderProductType;

// NOTE: History view now uses products directly from the order payload,
// so we no longer need to resolve them via external product lookups here.

export default function CardListView() {
  const {
    cartItems,
    removeItem,
    cartTotal,
    addItem,
    updateQty,
    cleanCart,
  } = useCart();
  const { setCardState } = useCard();
  const { cardViewState } = useCardView();
  const { currentHistoryItem } = HistoryCardState();

  const [messageCard, setMessageCard] = useState(false);
  const [message, setMessage] = useState("");
  const [isLoading, setIsLoading] = useState(false);
  const [jwt, setJwt] = useState<string | undefined>();
  const [isClient, setIsClient] = useState(false);

  useEffect(() => {
    setIsClient(true);
    setJwt(Cookies.get("jwt"));

    const storedCart = localStorage.getItem("cartItems");
    if (storedCart) {
      try {
        const parsedCart = JSON.parse(storedCart) as ProductType[];
        const restoredCart = parsedCart.map(
          (item: ProductType) => new Item(item, item.qty)
        );
        restoredCart.forEach((item: Item) => addItem(item));
      } catch (error) {
        console.error("Error parsing cart data:", error);
      }
    }
  }, [addItem]);

  const toLoginPage = () => {
    window.location.href = "/login";
  };

  const save_request = async () => {
    if (isLoading) return;
    setIsLoading(true);

    try {
      const clientCode = getCookie("code");
      const vetgroupUserId = getCookie("user");
      if (!clientCode || !vetgroupUserId) throw new Error("User not found");

      const freshTotal = useCart.getState().cartTotal;

      if (vetgroupUserId) {
        await add_order(
          cartItems,
          clientCode,
          freshTotal,
          vetgroupUserId
        );

        cleanCart();
        setMessageCard(true);

        setMessage("Պատվերը հաջողությամբ ուղարկվեց։");
      }
    } catch {
      setMessageCard(true);
      setMessage("Տեխնիկական խնդիր։");
    } finally {
      setTimeout(() => {
        setMessageCard(false);
        setCardState(false);
        setIsLoading(false);
      }, 1500);
      setTimeout(() => {
        // window.location.reload();
      }, 500);
    }
  };

  const formatPrice = (value: number): string => {
    if (isNaN(value)) return "0";
    const fixed = value.toFixed(2);
    const [intPart, decimal] = fixed.split(".");
    let formatted = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, " ");
    if (decimal !== "00") formatted += "," + decimal;
    return formatted;
  };

  const visibleItems: VisibleItems =
    cardViewState === CardView.History ? currentHistoryItem : cartItems;

  type HistoryRow = {
    name?: string;
    description?: string;
    ItemName?: string;
    ItemID?: string;
    qty?: number;
    Quantity?: number;
    quantity?: number;
    Qty?: number;
    price?: number;
    Price?: number;
    price_per_unit?: number;
  };

  const isHistory = cardViewState === CardView.History;

  return (
    <>
      <div className={style.cardList}>
        <div className={`${style.cardListRow} flex row`}>
          {!isHistory && (
            <div className={style.cardListRowTitle}>
              <span>Նկար</span>
            </div>
          )}
          <div className={style.cardListRowTitle}>
            <span>Անվանում</span>
          </div>
          <div className={style.cardListRowTitle}>
            <span>Քանակ</span>
          </div>
          {!isHistory && (
            <div className={style.cardListRowTitle}>
              <span>Գին</span>
            </div>
          )}
          <div className={style.cardListRowTitle}>
            <span>Ընդհանուր</span>
          </div>
        </div>

        <div className={style.cardListData}>
          {isHistory ? (
            Array.isArray(visibleItems) && visibleItems.length > 0 ? (
              (visibleItems as HistoryRow[]).map((row, idx) => {
                const name =
                  row.name ??
                  row.description ??
                  row.ItemName ??
                  row.ItemID ??
                  "";
                const qty =
                  row.qty ??
                  row.Quantity ??
                  row.quantity ??
                  row.Qty ??
                  0;
                const price =
                  row.price ??
                  row.Price ??
                  row.price_per_unit ??
                  0;

                return (
                  <div
                    className={`row flex ${style.cardListDataRow}`}
                    key={idx}
                  >
                    <div className={style.cardListDataRowElement}>
                      <span>{name}</span>
                    </div>
                    <div className={style.cardListDataRowElement}>
                      <span>{qty}</span>
                    </div>
                    <div className={`${style.cardListDataRowElement} flex`}>
                      <span>
                        {formatPrice(Number(price) * Number(qty || 0))} Դրամ
                      </span>
                    </div>
                  </div>
                );
              })
            ) : (
              <div className={style.cardListDataRow}>
                <span>Ապրանքներ չեն գտնվել։</span>
              </div>
            )
          ) : (
            // ProductType[] branch
            (visibleItems as ProductType[]).map((item, key) => {
              const imageUrl = item?.image?.url
                ? buildImageUrl(item.image.url)
                : "/placeholder.webp";
              return (
                <div
                  className={`row flex ${style.cardListDataRow}`}
                  key={item.code ?? key}
                >
                  {!isHistory && (
                    <div className={style.cardListDataRowElement}>
                      <div className={style.cardListDataRowElementImage}>
                        <ImageComponent
                          url={imageUrl}
                          alt={item.description ?? ""}
                        />
                      </div>
                    </div>
                  )}

                  <div className={style.cardListDataRowElement}>
                    <span>{item.description}</span>
                  </div>

                  <div className={style.cardListDataRowElement}>
                    {!isHistory ? (
                      <div className={style.qtyControls}>
                        <button
                          onClick={() =>
                            updateQty(
                              item.code,
                              Math.max(1, (item.qty ?? 1) - 1)
                            )
                          }
                          disabled={isHistory}
                        >
                          <ArrowSVG />
                        </button>
                        <input
                          type="number"
                          value={item.qty ?? 1}
                          onChange={(e) =>
                            updateQty(item.code, Number(e.target.value) || 1)
                          }
                          disabled={isHistory}
                          min={1}
                          inputMode="numeric"
                        />
                        <button
                          onClick={() =>
                            updateQty(item.code, (item.qty ?? 1) + 1)
                          }
                          disabled={isHistory}
                        >
                          <ArrowSVG />
                        </button>
                      </div>
                    ) : (
                      <span>{item.qty}</span>
                    )}
                  </div>

                  {!isHistory && (
                    <div className={style.cardListDataRowElement}>
                      <span>{formatPrice(item.price ?? 0)} Դրամ</span>
                    </div>
                  )}

                  <div className={`${style.cardListDataRowElement} flex`}>
                    <span>
                      {formatPrice((item.price ?? 0) * (item.qty ?? 1))} Դրամ
                    </span>
                    {!isHistory && (
                      <button
                        onClick={() =>
                          removeItem((item as unknown as Item).getId())
                        }
                      >
                        <TrashSVG />
                      </button>
                    )}
                  </div>
                </div>
              );
            })
          )}
        </div>
      </div>

      {!isHistory && (
        <div className={style.cardListCheckout}>
          <h1>
            Ընդհանուր: <span>{formatPrice(cartTotal)} Դրամ</span>
          </h1>
          {isClient && jwt ? (
            <button
              onClick={save_request}
              className={
                isLoading ||
                (Array.isArray(visibleItems) && visibleItems.length <= 0)
                  ? style.disabled
                  : ""
              }
              disabled={
                isLoading ||
                (Array.isArray(visibleItems) && visibleItems.length <= 0)
              }
            >
              {isLoading ? "Ուղարկում է..." : "Ուղարկել Պատվերը"}
            </button>
          ) : (
            isClient && <button onClick={toLoginPage}>Login</button>
          )}
        </div>
      )}

      {messageCard && <div className={style.message}>{message}</div>}
    </>
  );
}
