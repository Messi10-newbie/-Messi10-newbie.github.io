import javafx.collections.*;
import javafx.geometry.*;
import javafx.scene.*;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.scene.paint.Color;
import javafx.scene.text.*;
import javafx.stage.Stage;

import java.util.*;

public class CustomerMenu {

    private static String loggedInUser;

    public static void show(String username) {
        loggedInUser = username;
        Stage stage = Main.primaryStage;

        BorderPane root = new BorderPane();
        root.setStyle("-fx-background-color: #0d1117;");

        VBox sidebar = buildSidebar(root);
        root.setLeft(sidebar);
        root.setCenter(buildBrowseTab(root));

        stage.setScene(new Scene(root, 1000, 660));
        stage.setTitle("🎬 Cinema — " + username);
        stage.setResizable(true);
        stage.centerOnScreen();
        stage.show();
    }

    // ── Sidebar ────────────────────────────────────────────────────────────
    private static VBox buildSidebar(BorderPane root) {
        VBox sb = new VBox(6);
        sb.setPrefWidth(195);
        sb.setPadding(new Insets(20, 10, 20, 10));
        sb.setStyle("-fx-background-color: #161b22;");

        Text appTitle = new Text("🎬 CinemaBox");
        appTitle.setFont(Font.font("Arial", FontWeight.BOLD, 18));
        appTitle.setFill(Color.WHITE);

        Text who = new Text("Welcome, " + loggedInUser);
        who.setFont(Font.font(11)); who.setFill(Color.web("#8b949e"));

        Separator sep = new Separator();
        sep.setStyle("-fx-background-color: #30363d;");

        Button[] btns = {
            sideBtn("🎬  Browse Movies",  () -> root.setCenter(buildBrowseTab(root))),
            sideBtn("🎟  My Bookings",    () -> root.setCenter(buildMyBookingsTab()))
        };

        Region spacer = new Region(); VBox.setVgrow(spacer, Priority.ALWAYS);
        Button logoutBtn = sideBtn("🚪  Logout", LoginScreen::show);
        logoutBtn.setStyle(logoutBtn.getStyle().replace("#21262d", "#3d1515"));

        sb.getChildren().addAll(appTitle, who, sep);
        sb.getChildren().addAll(btns);
        sb.getChildren().addAll(spacer, logoutBtn);
        return sb;
    }

    private static Button sideBtn(String label, Runnable action) {
        Button b = new Button(label);
        b.setMaxWidth(Double.MAX_VALUE);
        b.setAlignment(Pos.CENTER_LEFT);
        b.setStyle("-fx-background-color: #21262d; -fx-text-fill: #c9d1d9;" +
                   "-fx-font-size: 13; -fx-padding: 10 14; -fx-background-radius: 8; -fx-cursor: hand;");
        b.setOnMouseEntered(e -> b.setStyle(b.getStyle().replace("#21262d","#30363d")));
        b.setOnMouseExited(e  -> b.setStyle(b.getStyle().replace("#30363d","#21262d")));
        b.setOnAction(e -> action.run());
        return b;
    }

    // ── Browse Movies ──────────────────────────────────────────────────────
    private static ScrollPane buildBrowseTab(BorderPane parent) {
        VBox box = new VBox(14);
        box.setPadding(new Insets(30));

        Text heading = new Text("🎬 Now Showing");
        heading.setFont(Font.font("Arial", FontWeight.BOLD, 22));
        heading.setFill(Color.WHITE);
        box.getChildren().add(heading);

        for (Movie m : MovieManager.loadAll()) {
            VBox card = new VBox(6);
            card.setPadding(new Insets(16));
            card.setStyle("-fx-background-color: #161b22; -fx-background-radius: 12;");

            HBox top = new HBox(10);
            top.setAlignment(Pos.CENTER_LEFT);
            VBox info = new VBox(3);
            Text t = new Text(m.getTitle()); t.setFont(Font.font("Arial",FontWeight.BOLD,15)); t.setFill(Color.WHITE);
            Text g = new Text(m.getGenre()+" | "+m.getDuration()+" | "+m.getRating());
            g.setFont(Font.font(12)); g.setFill(Color.web("#8b949e"));
            Text d = new Text(m.getDescription()); d.setFont(Font.font(12)); d.setFill(Color.web("#c9d1d9"));
            d.setWrappingWidth(480);
            info.getChildren().addAll(t, g, d);

            Region spacer = new Region(); HBox.setHgrow(spacer, Priority.ALWAYS);

            VBox right = new VBox(8);
            right.setAlignment(Pos.CENTER_RIGHT);
            Text price = new Text("From $"+m.getBasePrice());
            price.setFont(Font.font("Arial",FontWeight.BOLD,16)); price.setFill(Color.web("#e94560"));
            int avail = SeatManager.availableCount(m.getTitle());
            Label seats = new Label(avail+" seats");
            seats.setStyle("-fx-background-color: #1f6feb33; -fx-text-fill: #58a6ff;" +
                           "-fx-padding: 3 8; -fx-background-radius: 20; -fx-font-size: 11;");
            Button bookBtn = new Button("Book Now →");
            bookBtn.setStyle("-fx-background-color: #e94560; -fx-text-fill: white;" +
                             "-fx-font-size: 12; -fx-padding: 7 16; -fx-background-radius: 8; -fx-cursor: hand;");
            if (avail == 0) { bookBtn.setText("SOLD OUT"); bookBtn.setDisable(true); }
            bookBtn.setOnAction(e -> SeatSelectionScreen.show(loggedInUser, m, () -> parent.setCenter(buildBrowseTab(parent))));
            right.getChildren().addAll(price, seats, bookBtn);

            top.getChildren().addAll(info, spacer, right);
            card.getChildren().add(top);
            box.getChildren().add(card);
        }

        ScrollPane sp = new ScrollPane(box);
        sp.setFitToWidth(true);
        sp.setStyle("-fx-background: transparent; -fx-background-color: transparent;");
        return sp;
    }

    // ── My Bookings ────────────────────────────────────────────────────────
    private static ScrollPane buildMyBookingsTab() {
        VBox box = new VBox(14);
        box.setPadding(new Insets(30));

        Text heading = new Text("🎟 My Bookings");
        heading.setFont(Font.font("Arial", FontWeight.BOLD, 22));
        heading.setFill(Color.WHITE);
        box.getChildren().add(heading);

        List<Booking> myBookings = BookingManager.getBookingsForUser(loggedInUser);
        if (myBookings.isEmpty()) {
            Text none = new Text("No bookings yet. Go browse some movies!");
            none.setFill(Color.web("#8b949e")); none.setFont(Font.font(14));
            box.getChildren().add(none);
        } else {
            for (Booking b : myBookings) {
                HBox card = new HBox(14);
                card.setPadding(new Insets(14));
                card.setAlignment(Pos.CENTER_LEFT);
                card.setStyle("-fx-background-color: #161b22; -fx-background-radius: 10;");

                VBox info = new VBox(4);
                Text movie = new Text(b.getMovieTitle());
                movie.setFont(Font.font("Arial",FontWeight.BOLD,14)); movie.setFill(Color.WHITE);
                Text details = new Text("Seat: "+b.getSeatCode()+" ("+b.getSeatType()+") | " +
                    b.getBookingDate()+" | Snacks: "+b.getSnacks());
                details.setFont(Font.font(12)); details.setFill(Color.web("#8b949e"));
                Text price = new Text("$"+String.format("%.2f",b.getTotalPrice()));
                price.setFont(Font.font("Arial",FontWeight.BOLD,13)); price.setFill(Color.web("#38a169"));
                info.getChildren().addAll(movie, details, price);

                Region spacer = new Region(); HBox.setHgrow(spacer, Priority.ALWAYS);

                String statusColor = b.getStatus().equals("CONFIRMED") ? "#38a169" : "#fc8181";
                Label status = new Label(b.getStatus());
                status.setStyle("-fx-background-color: "+statusColor+"33; -fx-text-fill: "+statusColor+";" +
                                "-fx-padding: 4 10; -fx-background-radius: 20; -fx-font-size: 12;");

                card.getChildren().addAll(info, spacer, status);

                if (b.getStatus().equals("CONFIRMED")) {
                    Button cancelBtn = new Button("Cancel");
                    cancelBtn.setStyle("-fx-background-color: #3d1515; -fx-text-fill: #fc8181;" +
                                       "-fx-font-size: 12; -fx-padding: 5 12; -fx-background-radius: 6; -fx-cursor: hand;");
                    cancelBtn.setOnAction(e -> {
                        Alert a = new Alert(Alert.AlertType.CONFIRMATION, "Cancel this booking?", ButtonType.OK, ButtonType.CANCEL);
                        a.showAndWait().ifPresent(r -> {
                            if (r == ButtonType.OK) {
                                BookingManager.cancelBooking(b.getBookingId(), b.getMovieTitle(), b.getSeatCode());
                                // Refresh
                                box.getChildren().setAll(buildMyBookingsContent());
                            }
                        });
                    });
                    card.getChildren().add(cancelBtn);
                }
                box.getChildren().add(card);
            }
        }

        ScrollPane sp = new ScrollPane(box);
        sp.setFitToWidth(true);
        sp.setStyle("-fx-background: transparent; -fx-background-color: transparent;");
        return sp;
    }

    private static List<javafx.scene.Node> buildMyBookingsContent() {
        // just return child nodes for a refresh
        VBox tmp = new VBox();
        tmp.getChildren().addAll(buildMyBookingsTab().getContent() instanceof VBox
            ? ((VBox)buildMyBookingsTab().getContent()).getChildren() : Collections.emptyList());
        return tmp.getChildren();
    }
}
