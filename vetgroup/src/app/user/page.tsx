"use client";

import { useEffect, useState } from "react";
import Cookies from "js-cookie";
import { useRouter } from "next/navigation";
import Sidebar from "@/components/Elements/Sidebar/sidebar.component";
import UserPageContent from "@/components/UserComponents/UserPageContent/userPageContent.component";

type CurrentUserType = {
  documentId: string;
  first_name: string;
  last_name: string;
  company: string;
};

export default function UserPage() {
  const router = useRouter();
  const [currentUser, setCurrentUser] = useState<CurrentUserType | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const jwt = Cookies.get("jwt");
    const documentId = Cookies.get("document");
    const first_name = Cookies.get("first_name") || "";
    const last_name = Cookies.get("last_name") || "";
    const company = Cookies.get("company") || "";

    // If there is no session cookie, redirect to login
    if (!jwt || !documentId) {
      router.push("/login");
      return;
    }

    // Build current user directly from cookies set at login
    setCurrentUser({
      documentId,
      first_name,
      last_name,
      company,
    });
    setLoading(false);
  }, [router]);

  if (loading || !currentUser) return null;

  return (
    <main className="flex relative">
      <Sidebar current_user={currentUser} />
      <UserPageContent />
    </main>
  );
}
