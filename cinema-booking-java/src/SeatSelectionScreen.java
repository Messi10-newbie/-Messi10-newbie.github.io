import javafx.geometry.*;
import javafx.scene.*;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.scene.paint.Color;
import javafx.scene.text.*;
import javafx.stage.*;

public class SeatSelectionScreen {

    private static String selectedSeat = null;
    private static String selectedSnack = "None";

    public static void show(String username, Movie movie, Showtime showtime, Runnable onBooked) {
        selectedSeat  = null;
        selectedSnack = "None";

        Stage dlg = new Stage();
        dlg.setTitle("🎟 Book — " + movie.getTitle());
        dlg.initOwner(Main.primaryStage);
        dlg.initModality(Modality.APPLICATION_MODAL);

        BorderPane root = new BorderPane();
        root.setStyle("-fx-background-color: #0d1117;");
        root.setPadding(new Insets(24));

        // ── Top ────────────────────────────────────────────────────────────
        Text heading = new Text("Select Your Seat — " + movie.getTitle());
        heading.setFont(Font.font("Arial", FontWeight.BOLD, 18));
        heading.setFill(Color.WHITE);

        Text showtimeInfo = new Text("📅 " + showtime.getDisplay());
        showtimeInfo.setFont(Font.font(12));
        showtimeInfo.setFill(Color.web("#58a6ff"));

        Text subtitle = new Text("🟥 VIP (rows A-C)   🟦 Standard (D-G)   ⬛ Economy (H-J)   ✕ Booked");
        subtitle.setFont(Font.font(11));
        subtitle.setFill(Color.web("#8b949e"));

        StackPane screen = new StackPane();
        screen.setPadding(new Insets(6));
        screen.setStyle("-fx-background-color: #ffffff22; -fx-background-radius: 6;");
        Text screenLbl = new Text("── SCREEN ──");
        screenLbl.setFill(Color.web("#8b949e")); screenLbl.setFont(Font.font(11));
        screen.getChildren().add(screenLbl);

        VBox topBox = new VBox(6, heading, showtimeInfo, subtitle, screen);
        topBox.setAlignment(Pos.CENTER);
        topBox.setPadding(new Insets(0, 0, 10, 0));
        root.setTop(topBox);

        // ── Seat grid ──────────────────────────────────────────────────────
        boolean[][] seats = SeatManager.load(showtime.getId());

        GridPane grid = new GridPane();
        grid.setHgap(5); grid.setVgap(5);
        grid.setAlignment(Pos.CENTER);

        for (int c = 0; c < SeatManager.COLS; c++) {
            Label h = new Label(String.valueOf(c + 1));
            h.setStyle("-fx-text-fill: #8b949e; -fx-font-size: 11;");
            h.setAlignment(Pos.CENTER);
            h.setMinWidth(38);
            grid.add(h, c + 1, 0);
        }

        ToggleGroup group = new ToggleGroup();
        for (int r = 0; r < SeatManager.ROWS; r++) {
            Label rowLbl = new Label(String.valueOf((char)('A' + r)));
            rowLbl.setStyle("-fx-text-fill: #8b949e; -fx-font-size: 11;");
            rowLbl.setMinWidth(20);
            rowLbl.setAlignment(Pos.CENTER);
            grid.add(rowLbl, 0, r + 1);

            String type       = SeatManager.getSeatType(r);
            String color      = type.equals("VIP") ? "#7f1d1d" : type.equals("Standard") ? "#1e3a5f" : "#1c1c1c";
            String hoverColor = type.equals("VIP") ? "#b91c1c" : type.equals("Standard") ? "#2563eb" : "#374151";
            String selectColor = "#e94560";

            for (int c = 0; c < SeatManager.COLS; c++) {
                boolean booked = seats[r][c];
                ToggleButton btn = new ToggleButton(booked ? "✕" : SeatManager.toCode(r, c));
                btn.setMinWidth(38); btn.setMinHeight(34);
                btn.setToggleGroup(group);
                btn.setDisable(booked);
                final int rr = r, cc = c;

                String baseStyle = "-fx-background-color: " + (booked ? "#111" : color) + ";" +
                    "-fx-text-fill: " + (booked ? "#555" : "white") + ";" +
                    "-fx-font-size: 10; -fx-background-radius: 5; -fx-cursor: " + (booked ? "default" : "hand") + ";";
                btn.setStyle(baseStyle);

                btn.selectedProperty().addListener((obs, was, now) -> {
                    if (now) { btn.setStyle(baseStyle.replace(color, selectColor)); selectedSeat = SeatManager.toCode(rr, cc); }
                    else     { btn.setStyle(baseStyle); }
                });
                btn.setOnMouseEntered(e -> { if (!btn.isSelected() && !btn.isDisabled()) btn.setStyle(baseStyle.replace(color, hoverColor)); });
                btn.setOnMouseExited(e  -> { if (!btn.isSelected() && !btn.isDisabled()) btn.setStyle(baseStyle); });

                grid.add(btn, c + 1, r + 1);
            }
        }

        ScrollPane gridScroll = new ScrollPane(grid);
        gridScroll.setFitToWidth(true);
        gridScroll.setStyle("-fx-background: transparent; -fx-background-color: transparent;");
        root.setCenter(gridScroll);

        // ── Right panel: Snacks + Summary ──────────────────────────────────
        VBox rightPanel = new VBox(14);
        rightPanel.setPadding(new Insets(0, 0, 0, 20));
        rightPanel.setMinWidth(220);

        Text snackTitle = txt("🍿 Snacks", 14, true);
        ComboBox<String> snackBox = new ComboBox<>();
        snackBox.getItems().addAll(PriceCalculator.SNACK_PRICES.keySet());
        snackBox.setValue("None");
        snackBox.setMaxWidth(Double.MAX_VALUE);
        snackBox.setStyle("-fx-background-color: #21262d; -fx-text-fill: white;");

        Label summaryLbl = new Label("Select a seat to see price");
        summaryLbl.setStyle("-fx-text-fill: #c9d1d9; -fx-font-size: 12; -fx-font-family: monospace;");
        summaryLbl.setWrapText(true);

        Button refreshSummary = new Button("↻ Refresh Price");
        refreshSummary.setStyle("-fx-background-color: #21262d; -fx-text-fill: #c9d1d9;" +
                                "-fx-font-size: 12; -fx-padding: 7; -fx-background-radius: 6; -fx-cursor: hand;");
        refreshSummary.setOnAction(e -> {
            selectedSnack = snackBox.getValue();
            if (selectedSeat == null) { summaryLbl.setText("Please select a seat first."); return; }
            int[] rc = SeatManager.parseCode(selectedSeat);
            summaryLbl.setText(PriceCalculator.breakdown(movie.getBasePrice(), SeatManager.getSeatType(rc[0]), selectedSnack));
        });

        Label errLbl = new Label();
        errLbl.setStyle("-fx-text-fill: #fc8181; -fx-font-size: 12;");
        errLbl.setWrapText(true);

        Button bookBtn = new Button("✔ Confirm Booking");
        bookBtn.setMaxWidth(Double.MAX_VALUE);
        bookBtn.setStyle("-fx-background-color: #38a169; -fx-text-fill: white;" +
                         "-fx-font-size: 14; -fx-font-weight: bold; -fx-padding: 12; -fx-background-radius: 8; -fx-cursor: hand;");

        bookBtn.setOnAction(e -> {
            errLbl.setText("");
            if (selectedSeat == null) { errLbl.setText("Please select a seat!"); return; }
            selectedSnack = snackBox.getValue();
            int[] rc = SeatManager.parseCode(selectedSeat);
            String seatType = SeatManager.getSeatType(rc[0]);
            double total = PriceCalculator.totalPrice(movie.getBasePrice(), seatType, selectedSnack);

            boolean ok = SeatManager.bookSeat(showtime.getId(), selectedSeat);
            if (!ok) { errLbl.setText("Seat just got taken! Please choose another."); return; }

            Booking b = Booking.create(username, movie.getTitle(), selectedSeat, seatType,
                                       total, selectedSnack, showtime.getId(), showtime.getDisplay());
            BookingManager.addBooking(b);
            showConfirmation(b, showtime, dlg, onBooked);
        });

        rightPanel.getChildren().addAll(snackTitle, snackBox, refreshSummary,
            new Separator(), txt("💰 Price Summary", 13, true), summaryLbl,
            errLbl, bookBtn);
        root.setRight(rightPanel);

        dlg.setScene(new Scene(root, 820, 580));
        dlg.show();
    }

    private static void showConfirmation(Booking b, Showtime showtime, Stage parent, Runnable onBooked) {
        Stage confirm = new Stage();
        confirm.setTitle("Booking Confirmed!");
        confirm.initOwner(parent);

        VBox box = new VBox(14);
        box.setPadding(new Insets(30));
        box.setAlignment(Pos.CENTER);
        box.setStyle("-fx-background-color: #0d1117;");

        Text icon = new Text("✅"); icon.setFont(Font.font(50));
        Text title = new Text("Booking Confirmed!");
        title.setFont(Font.font("Arial", FontWeight.BOLD, 22)); title.setFill(Color.web("#38a169"));

        String info = String.format(
            "Booking ID : %s%nMovie      : %s%nShowtime   : %s%nSeat       : %s (%s)%nSnacks     : %s%nTotal Paid : $%.2f%nBooked On  : %s",
            b.getBookingId(), b.getMovieTitle(), showtime.getDisplay(),
            b.getSeatCode(), b.getSeatType(),
            b.getSnacks(), b.getTotalPrice(), b.getBookingDate()
        );
        Label details = new Label(info);
        details.setStyle("-fx-text-fill: #c9d1d9; -fx-font-family: monospace; -fx-font-size: 13;");
        details.setAlignment(Pos.CENTER_LEFT);

        Button close = new Button("Great, Done!");
        close.setStyle("-fx-background-color: #38a169; -fx-text-fill: white;" +
                       "-fx-font-size: 14; -fx-padding: 10 30; -fx-background-radius: 8; -fx-cursor: hand;");
        close.setOnAction(e -> { confirm.close(); parent.close(); onBooked.run(); });

        box.getChildren().addAll(icon, title, details, close);
        confirm.setScene(new Scene(box, 420, 360));
        confirm.show();
    }

    private static Text txt(String s, int size, boolean bold) {
        Text t = new Text(s);
        t.setFont(Font.font("Arial", bold ? FontWeight.BOLD : FontWeight.NORMAL, size));
        t.setFill(Color.WHITE);
        return t;
    }
}
