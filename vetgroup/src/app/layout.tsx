import "../../startup";
import "@/styles/main.scss";
import "@/styles/globals.scss";
import type { Metadata } from "next";
import Card from "@/components/CardComponents/Card/card.component";
import { Montserrat } from "next/font/google";
import Head from "next/head";

const montserrat = Montserrat({
  subsets: ["latin"],
});

export const metadata: Metadata = {
  title: "VetGroup",
};

export default function RootLayout({
  children,
}: Readonly<{ children: React.ReactNode }>) {
  return (
    <html lang="en">
      <Head>
        <meta
          name="viewport"
          content="width=device-width, initial-scale=1, viewport-fit=cover"
        ></meta>
      </Head>
      <body className={montserrat.className}>
        {children}
        <Card />
      </body>
    </html>
  );
}
