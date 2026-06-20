import { describe, it, expect, vi } from "vitest";
import { screen, waitFor } from "@testing-library/react";
import { renderWithQueryClient } from "@/test/render-with-providers";
import ApkPage from "./page";

vi.mock("next-auth/react", () => ({
  useSession: () => ({
    data: { user: { admin: 1, type: 1, module: 4 } },
    status: "authenticated",
  }),
}));

vi.mock("@/lib/api/apk.service", () => ({
  fetchApkList: vi.fn().mockResolvedValue({ data: [], total: 0, lastPage: 1 }),
}));

describe("ApkPage", () => {
  it("renders without throwing and shows the Add Account button", async () => {
    renderWithQueryClient(<ApkPage />);

    expect(screen.getByText("APK Checker")).toBeTruthy();

    await waitFor(() => {
      expect(screen.getByText("0 scanner accounts")).toBeTruthy();
    });

    expect(screen.getByRole("button", { name: /add account/i })).toBeTruthy();
  });
});
