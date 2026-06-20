import { describe, it, expect, vi } from "vitest";
import { screen, waitFor } from "@testing-library/react";
import { renderWithQueryClient } from "@/test/render-with-providers";
import UserManagementPage from "./page";

vi.mock("next-auth/react", () => ({
  useSession: () => ({
    data: { user: { admin: 1, type: 4, module: 4 } },
    status: "authenticated",
  }),
}));

vi.mock("@/lib/api/user.service", () => ({
  fetchUserList: vi.fn().mockResolvedValue({ data: [], total: 0, lastPage: 1 }),
}));

describe("UserManagementPage", () => {
  it("renders without throwing and shows the Add button for an admin session", async () => {
    renderWithQueryClient(<UserManagementPage />);

    expect(screen.getByText("User Management")).toBeTruthy();

    await waitFor(() => {
      expect(screen.getByText("0 users terdaftar")).toBeTruthy();
    });

    expect(screen.getByRole("button", { name: /add user/i })).toBeTruthy();
  });
});
